<template>
    <div>
        <Navbar />
        <div class="settings p-5 m-5">
            <div class="container">
                <form>
                    <h1>General Settings</h1>
                    <div class="form-group">
                        <label for="name">Name</label>
                        <input
                            type="text"
                            class="form-control"
                            id="name"
                            :value="user.name"
                            @input="data.name = $event.target.value"
                        />
                        <a class="text text-danger" v-if="errors.name">{{
                            errors.name[0]
                        }}</a>
                    </div>

                    <div class="form-group">
                        <label for="email">Email address</label>
                        <input
                            type="email"
                            class="form-control"
                            id="email"
                            :value="user.email"
                            @input="data.email = $event.target.value"
                        />
                        <a class="text text-danger" v-if="errors.email">{{
                            errors.email[0]
                        }}</a>
                    </div>
                    <div class="form-group">
                        <label for="username">username</label>
                        <input
                            type="text"
                            class="form-control"
                            id="username"
                            :value="user.username"
                            @input="data.username = $event.target.value"
                        />
                        <a class="text text-danger" v-if="errors.username">{{
                            errors.username[0]
                        }}</a>
                    </div>
                    <div class="form-group">
                        <label for="profile_pic">Profile Pic</label>
                        <input
                            type="file"
                            accept="image/*"
                            class="form-control-file"
                            id="profile_pic"
                            @change="uploadImg($event)"
                        />
                        <a class="text text-danger" v-if="errors.picture">{{
                            errors.picture[0]
                        }}</a>
                    </div>

                    <div class="form-group">
                        <label for="password">Confirm your password</label>
                        <input
                            type="password"
                            class="form-control"
                            id="password"
                            placeholder="Your Password"
                            v-model="data.password"
                        />
                        <a class="text text-danger" v-if="errors.password">{{
                            errors.password[0]
                        }}</a>
                    </div>

                    <button
                        type="submit"
                        class="btn btn-primary"
                        @click.prevent="update"
                    >
                        Update
                    </button>
                </form>
            </div>
        </div>
        <Footer />
    </div>
</template>

<script>
import Navbar from "../../components/Layout/Navbar.vue";
import Footer from "../../components/Layout/Footer.vue";
import { mapActions, mapGetters } from "vuex";
export default {
    data() {
        return {
            errors: {},
            data: {
                name: "",
                email: "",
                username: "",
                password: ""
            },
            picture: null
        };
    },
    components: {
        Navbar,
        Footer
    },
    computed: mapGetters("Auth", ["user"]),
    methods: {
        ...mapActions("Auth", ["fetchMe"]),
        update() {
            this.errors = {};
            let fd = new FormData();
            fd.append("name", this.data.name);
            fd.append("username", this.data.username);
            fd.append("email", this.data.email);
            fd.append("password", this.data.password);
            fd.append("picture", this.picture, this.picture.name);
            this.$http
                .post(`settings/update`, fd, {
                    headers: {
                        Authorization:
                            "Bearer " + this.$store.state.Auth.access_token
                    }
                })
                .then(response => {
                    this.fetchMe();
                    alertify.notify(
                        "Your Settings has been updated.",
                        "success"
                    );
                })
                .catch(error => {
                    this.errors = error.data.errors;
                    alertify.notify(
                        "Your Settings hasn't been updated.",
                        "error"
                    );
                });
        },
        uploadImg(event) {
            this.picture = event.target.files[0];
        }
    },
    mounted() {
        this.fetchMe().then(s => console.log(this.user));
    }
};
</script>
