<template>
    <div>
        <Navbar />
        <div class="page-content">
            <div class="container">
                <div class="row">
                    <div class="col-lg-3 me">
                        <div class="img">
                            <img
                                :src="user.pic ? user.pic : DEFAULT_PIC"
                                class="img-fluid"
                            />
                        </div>
                        <div class="about">
                            <h3 v-if="user.name">{{ user.name }}</h3>
                            <router-link
                                v-if="user.username"
                                :to="{
                                    name: 'user.send',
                                    params: { username: user.username }
                                }"
                                >{{ WEB_URL + user.username }}</router-link
                            >
                            <div class="row">
                                <div class="col-6">
                                    <router-link :to="{ name: 'settings' }">
                                        <button class="col-12 btn btn-primary">
                                            Settings
                                        </button>
                                    </router-link>
                                </div>
                                <div class="col-6">
                                    <button
                                        class="col-12 btn btn-danger"
                                        @click="deactive"
                                    >
                                        Deactivate
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-9 messages">
                        <h3
                            class="text-center alert alert-success p-3"
                            v-if="
                                typeof messages.data === 'object' &&
                                    messages.data.length === 0
                            "
                        >
                            You have no messages yet. Share you link to start
                            getting honest messages.
                            <br />
                            <router-link
                                v-if="user.username"
                                class="h5"
                                :to="{
                                    name: 'user.send',
                                    params: { username: user.username }
                                }"
                                >{{ WEB_URL + user.username }}</router-link
                            >
                        </h3>
                        <div
                            class="message"
                            v-for="message in messages.data"
                            :key="message.id"
                        >
                            <template v-if="message.message">
                                <p dir="auto">{{ message.message }}</p>
                            </template>
                            <template v-else>
                                <Record
                                    :url="message.record"
                                    :parent="{
                                        placeholder: 'Message #' + message.id,
                                        id: message.id
                                    }"
                                />
                            </template>
                            <i
                                class="fa fa-caret-down"
                                id="messageSettings"
                                data-toggle="dropdown"
                                aria-haspopup="true"
                                aria-expanded="false"
                            ></i>
                            <div
                                class="dropdown-menu"
                                aria-labelledby="messageSettings"
                            >
                                <div
                                    class="dropdown-item"
                                    @click="deleteMsg(message.id)"
                                >
                                    <i class="fa fa-trash fl-left"></i>
                                    Delete
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Footer />
    </div>
</template>

<script>
import { mapActions, mapGetters } from "vuex";
import Navbar from "../../components/Layout/Navbar.vue";
import Footer from "../../components/Layout/Footer.vue";
import Record from "../../components/Recorder/Record.vue";

export default {
    data() {
        return {
            DEFAULT_PIC: process.env.MIX_DEFAULT_PIC,
            WEB_URL: process.env.MIX_WEB_URL
        };
    },
    components: {
        Navbar,
        Footer,
        Record
    },
    methods: {
        ...mapActions("Message", ["deleteMsg", "fetchMessages"]),
        ...mapActions("Auth", ["fetchMe", "deleteAccount"]),
        deactive() {
            alertify.confirm(
                "DELETE ACCOUNT",
                "Are you sure you want to delete your account? You can't restore it and all data will be deleted permenantly.",
                () => {
                    this.deleteAccount();
                    alertify.success("YOUR ACCOUNT IS NOW DELETED");
                },
                function() {}
            );
        }
    },
    computed: {
        ...mapGetters("Message", ["messages"]),
        ...mapGetters("Auth", ["user"])
    },
    mounted() {
        this.fetchMe();
        // Fetch Messages
        this.fetchMessages();
    }
};
</script>

<style lang="scss" scoped>
.page-content {
    margin-top: 30px;
    .me {
        text-align: center;
        .img {
            max-height: 255px;
            max-width: 255px;
            border-radius: 50%;
            overflow: hidden;
            margin: auto;
            img {
                height: 255px;
            }
        }
        .about {
            margin: 10px 0 20px 0;
            font-family: Cairo;
            h3 {
                font-weight: bold;
                text-align: center;
                margin: 0;
            }
            a {
                color: #28c3d4;
                text-overflow: ellipsis;
                white-space: nowrap;
                display: block;
                overflow: hidden;
                margin-bottom: 10px;
            }
            button {
                font-size: 0.85rem;
            }
        }
    }
    .messages {
        .message {
            background: #fff;
            padding: 15px 40px 15px 20px;
            border-radius: 15px;
            text-align: justify;
            position: relative;
            margin-bottom: 20px;
            .track {
                max-width: 300px;
                border-radius: 10px;
                border: 1px solid #eee;
            }
            svg {
                color: #248ea9;
            }
            .dropdown-item {
                cursor: pointer;
            }
            & > svg {
                position: absolute;
                top: 17.5px;
                right: 15px;
                cursor: pointer;
            }
        }
    }
}
</style>
