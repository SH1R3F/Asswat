<template>
    <div class="recorder-panel">
        <button
            style="display: flex; justify-content: center; align-items: center;"
            class="voice-button"
            :class="{ resume: paused, pause: !paused }"
            :disabled="!recording"
            @click="toggleRecording"
        ></button>
        <button
            style="display: flex; justify-content: center; align-items: center;"
            class="voice-button record"
            @click="startRecording"
            :disabled="recording || recorded"
        ></button>
        <button
            style="display: flex; justify-content: center; align-items: center;"
            class="voice-button stop"
            :disabled="!recording"
            @click="stopRecording"
        ></button>
    </div>
</template>

<script>
import { mapGetters, mapMutations } from "vuex";
export default {
    data() {
        return {
            recording: false,
            paused: false,
            gumStream: null,
            rec: null
        };
    },
    computed: mapGetters("Message", ["recorded"]),
    methods: {
        ...mapMutations("Message", ["setRecorded", "setBlob", "setUrl"]),
        startRecording() {
            this.recording = true;
            navigator.mediaDevices
                .getUserMedia({
                    audio: true,
                    video: false
                })
                .then(stream => {
                    this.gumStream = stream;
                    var AudioContext =
                        window.AudioContext || window.webkitAudioContext;
                    var audioContext = new AudioContext();
                    var input = audioContext.createMediaStreamSource(stream);
                    this.rec = new Recorder(input, { numChannels: 1 });
                    this.rec.record();
                })
                .catch(err => console.log("err"));
        },
        toggleRecording() {
            if (this.rec.recording) {
                //pause
                this.rec.stop();
                this.paused = true;
            } else {
                //resume
                this.rec.record();
                this.paused = false;
            }
        },
        stopRecording() {
            this.setRecorded(true);
            this.recording = false;
            this.rec.stop(); //stop microphone access
            this.gumStream.getAudioTracks()[0].stop();
            //create the wav blob and pass it on to createDownloadLink
            this.rec.exportWAV(this.createDownloadLink);
        },
        createDownloadLink(blob) {
            // Real Voice
            // this.setUrl(url);
            // this.setBlob(blob);
            var URL = window.URL || window.webkitURL;
            var url = URL.createObjectURL(blob);
            // Fake voice !!!!
            this.changeVoice(url);
        },
        async changeVoice(audioURL) {
            let arrayBuffer = await (await fetch(audioURL)).arrayBuffer();
            var globalAudioBuffer = await new AudioContext().decodeAudioData(
                arrayBuffer
            );
            let outputAudioBuffer = globalAudioBuffer;
            outputAudioBuffer = await this.pitchShiftTransformer(
                outputAudioBuffer,
                {
                    shift: 0.83
                }
            );
            let outputWavBlob = await this.audioBufferToWaveBlob(
                outputAudioBuffer
            );
            let audioUrl = window.URL.createObjectURL(outputWavBlob);
            this.setUrl(audioUrl);
            this.setBlob(outputWavBlob);
        },
        async pitchShiftTransformer(
            audioBuffer,
            opts /*negative=lower, positive=higher*/
        ) {
            opts.shift = opts.shift === undefined ? 1 : opts.shift;
            let ctx = new OfflineAudioContext(
                audioBuffer.numberOfChannels,
                audioBuffer.length,
                audioBuffer.sampleRate
            );
            let source = ctx.createBufferSource();
            source.buffer = audioBuffer;
            let pitchChangeEffect = new Jungle(ctx);
            let compressor = ctx.createDynamicsCompressor();
            source.connect(pitchChangeEffect.input);
            pitchChangeEffect.output.connect(compressor);
            pitchChangeEffect.setPitchOffset(opts.shift);
            compressor.connect(ctx.destination);
            source.start(0);
            return await ctx.startRendering();
        },
        async audioBufferToWaveBlob(audioBuffer) {
            var waveWorkerString = `self.onmessage = function(e) { var wavPCM = new WavePCM(e['data']['config']); wavPCM.record(e['data']['pcmArrays']); wavPCM.requestData(); }; var WavePCM = function(config) { this.sampleRate = config['sampleRate'] || 48000; this.bitDepth = config['bitDepth'] || 16; this.recordedBuffers = []; this.bytesPerSample = this.bitDepth / 8; }; WavePCM.prototype.record = function(buffers) { this.numberOfChannels = this.numberOfChannels || buffers.length; var bufferLength = buffers[0].length; var reducedData = new Uint8Array(bufferLength * this.numberOfChannels * this.bytesPerSample); for (var i = 0; i < bufferLength; i++) { for (var channel = 0; channel < this.numberOfChannels; channel++) { var outputIndex = (i * this.numberOfChannels + channel) * this.bytesPerSample; var sample = buffers[channel][i]; if (sample > 1) { sample = 1; } else if (sample < -1) { sample = -1; } switch (this.bytesPerSample) { case 4: sample = sample * 2147483648; reducedData[outputIndex] = sample; reducedData[outputIndex + 1] = sample >> 8; reducedData[outputIndex + 2] = sample >> 16; reducedData[outputIndex + 3] = sample >> 24; break; case 3: sample = sample * 8388608; reducedData[outputIndex] = sample; reducedData[outputIndex + 1] = sample >> 8; reducedData[outputIndex + 2] = sample >> 16; break; case 2: sample = sample * 32768; reducedData[outputIndex] = sample; reducedData[outputIndex + 1] = sample >> 8; break; case 1: reducedData[outputIndex] = (sample + 1) * 128; break; default: throw "Only 8, 16, 24 and 32 bits per sample are supported"; } } } this.recordedBuffers.push(reducedData); }; WavePCM.prototype.requestData = function() { var bufferLength = this.recordedBuffers[0].length; var dataLength = this.recordedBuffers.length * bufferLength; var headerLength = 44; var wav = new Uint8Array(headerLength + dataLength); var view = new DataView(wav.buffer); view.setUint32(0, 1380533830, false); view.setUint32(4, 36 + dataLength, true); view.setUint32(8, 1463899717, false); view.setUint32(12, 1718449184, false); view.setUint32(16, 16, true); view.setUint16(20, 1, true); view.setUint16(22, this.numberOfChannels, true); view.setUint32(24, this.sampleRate, true); view.setUint32(28, this.sampleRate * this.bytesPerSample * this.numberOfChannels, true); view.setUint16(32, this.bytesPerSample * this.numberOfChannels, true); view.setUint16(34, this.bitDepth, true); view.setUint32(36, 1684108385, false); view.setUint32(40, dataLength, true); for (var i = 0; i < this.recordedBuffers.length; i++) { wav.set(this.recordedBuffers[i], i * bufferLength + headerLength); } self.postMessage(wav, [wav.buffer]); self.close(); };`;
            return new Promise(function(resolve, reject) {
                var worker = new Worker(
                    URL.createObjectURL(
                        new Blob([waveWorkerString], {
                            type: "application/javascript; charset=utf-8"
                        })
                    )
                );
                worker.onmessage = function(e) {
                    var blob = new Blob([e.data.buffer], { type: "audio/wav" });
                    resolve(blob);
                };
                let pcmArrays = [];
                for (let i = 0; i < audioBuffer.numberOfChannels; i++) {
                    pcmArrays.push(audioBuffer.getChannelData(i));
                }
                worker.postMessage({
                    pcmArrays,
                    config: { sampleRate: audioBuffer.sampleRate }
                });
            });
        }
    }
};
</script>

<style lang="scss" scoped>
.recorder-panel {
    display: flex;
    padding: 30px;
    justify-content: center;
    .voice-button {
        outline: none;
        width: 13vw;
        max-width: 100px;
        height: 13vw;
        max-height: 100px;
        border-radius: 50%;
        background: white;
        border: 1px solid #eee;
        position: relative;
        cursor: pointer;
        box-shadow: 0 0 2px rgba(0, 0, 0, 0.2);
        transition: box-shadow 0.3s;
        display: flex;
        justify-content: center;
        margin: 0 10px;
        &:hover {
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.2);
        }
        &:disabled {
            cursor: no-drop;
            opacity: 0.4;
        }
        &::before,
        &::after {
            content: "";
            display: block;
            background: tomato;
        }
        &.record::before {
            width: 5vw;
            max-width: 25px;
            height: 5vw;
            max-height: 25px;
            border-radius: 50%;
        }
        &.record::after {
            display: none;
        }

        &.resume::after {
            display: none;
        }

        &.resume::before {
            width: 0;
            height: 0;
            background: transparent;
            border-left: solid 25px tomato;
            border-bottom: solid 15px transparent;
            border-top: solid 15px transparent;
            margin-left: 8px;
            @media screen and (max-width: 700px) {
                border-left: solid 5vw tomato;
                border-bottom: solid 3vw transparent;
                border-top: solid 3vw transparent;
            }
        }

        &.pause {
            display: flex;
            justify-content: center;
        }

        &.pause::before,
        &.pause::after {
            width: 1.6vw;
            max-width: 10px;
            height: 5vw;
            max-height: 25px;
        }

        &.pause::after {
            margin-left: 5px;
        }

        &.stop::after {
            content: "\2713";
            background: none;
            color: tomato;
            font-size: 50px;
            @media screen and (max-width: 700px) {
                font-size: 5vw;
            }
        }
    }
}
</style>
