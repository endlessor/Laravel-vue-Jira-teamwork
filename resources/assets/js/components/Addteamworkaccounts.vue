<template>
    <div>
        <h3>Projects</h3>

        <!-- Jira project table -->
        <table class="table table-striped jira-table" style="width:40%">
            <thead>
                <tr>
                    <th class="id">ID</th>
                    <th class="name">Name</th>
                    <th class="key">Key</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="project in projects" v-bind:key="project.id">
                    <td class="id">{{ project.id }}</td>
                    <td class="name">
                        <router-link :to="{ name: 'setlinkedproject', params: { selectedProject: project }}">
                            <span class="custom-underline">{{ project.name }}</span>                            
                        </router-link>
                    </td>
                    <td class="key">{{ project.key }}</td>
                </tr>
            </tbody>
        </table><br/>

        <h3>Teamwork accounts</h3>
        <!-- Teamwork accounts table -->
        <table class="table table-striped aui jira-table">
            <thead>
                <tr>
                    <th class="id">ID</th>
                    <th class="url">Url</th>
                    <th class="key">Key</th>
                    <th class="status">Status</th>
                    <th class="action">Actions</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="app in teamworkApps" v-bind:key="app.id">
                    <td class="id">{{ app.id }}</td>
                    <td class="url">{{ app.url }}</td>
                    <td class="key">{{ app.token }}</td>
                    <td class="status">Connected</td>
                    <td>
                        <a v-on:click="delete_teamworkapp(app)" class="custom-underline">Remove</a>-
                        <a v-on:click="edit_teamworkapp(app)" class="custom-underline">Edit</a>-
                        <a v-on:click="sync_teamworkapp(app)" class="custom-underline" data-toggle="modal" data-target="#myModal">Sync now</a>
                    </td>
                </tr>
            </tbody>
        </table><br/><br/>

        <!-- Add new teamwork account part -->
        <a v-on:click="show_add_teamworkaccount" class="text-inborder">add account</a>
        <div v-if="teamaddform_visible" class="add-account">
            <table class="add-account-table">
                <tbody>
                    <tr>
                        <td width="35%">Teamwork domail:</td>
                        <td><input type="text" v-model="teamworkapp_url" class="form-control"></td>
                    </tr>
                    <tr>
                        <td width="35%">API access key: 
                            <a href="https://support.teamwork.com/desk/my-profile/how-can-i-generate-an-api-key" 
                                target="_blank" class="custom-underline"> Help? </a>
                        </td>
                        <td><input type="text" v-model="teamworkapp_token" class="form-control"></td>
                    </tr>
                </tbody>
            </table>
            <!-- error message out -->
            <p class="err-message" v-if="err_status">{{err_message}}</p>

            <div class="center">                
                <button class="btn3d btn btn-default" @click="post_teamworkapp">Connect</button>
                <span v-if="connect_status">Connecting...</span>
            </div>                        
        </div>

        <!-- teamwork account sync modal -->
        <div class="modal fade" id="myModal" role="dialog">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <h4 class="modal-title">Teamwork Sync</h4>
                    </div>
                    <div class="modal-body">
                        <iframe v-if="sync_status" style="width:1000px; height:600px;" :src="sync_url"></iframe>
                        <img v-if="!sync_status" src="/img/loading.gif">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>                
<script>
    export default {
        data() {
            return {
                projects: [],
                teamworkApps: [],
                teamworkapp_url:'',
                teamworkapp_token:'',
                teamaddform_visible:false,
                connect_status:false,
                err_status:false,
                err_message:'',
                showDialog:false,
                sync_url:'',
                curTeamworkappId:0,
                sync_status:false,
            }
        },

        mounted() {
            axios.get('api/v1/projects').then(
                (projects) => {
                    this.projects = projects.data.items;
                }
            );

            axios.get('api/v1/teamwork/apps').then(
                (projects) => {
                    this.teamworkApps = projects.data.items;
                }
            );
            this.teamaddform_visible = false;
            this.connect_status = false;
            this.err_message = '';
            this.err_status = false;
        },

        methods: {
            // add new teamwork account
            async post_teamworkapp(){
                this.err_status = false;
                if(this.teamworkapp_url == "" || this.teamworkapp_token == ""){
                    this.err_status = true;
                    this.err_message = "The account url or token can not be empty!";
                }
                if(this.err_status)return;

                //edit case
                this.connect_status = true;
                if(this.curTeamworkappId > 0){
                    await axios.put('api/v1/teamwork/apps/' + this.curTeamworkappId, {
                            url: this.teamworkapp_url,
                            token: this.teamworkapp_token
                        }).then((response) => { 
                            this.teamworkApps.map((app) => {
                                if(app.id == response.data.id){
                                    app.teamworkapp_url = response.data.teamworkapp_url;
                                    app.teamworkapp_token = response.data.teamworkapp_token;
                                }
                            });
                            this.curTeamworkappId = 0;
                            this.$notify({
                                group: 'foo',
                                duration: 5000,
                                type: 'success',
                                speed: 1000,
                                title: 'Edit Success',
                                text: 'Your accout edited successfully'
                            });
                            this.teamworkapp_url = "";
                            this.teamworkapp_token = "";
                        })
                        .catch((error) => {
                            this.$notify({
                                group: 'foo',
                                duration: 5000,
                                type: 'error',
                                speed: 1000,
                                title: 'Connection Error',
                                text: 'Can not change this account<br/>Please check your account info or connection and try again'
                            });
                        });                   
                }
                //create case
                else{
                    var exist = false;
                    this.teamworkApps.map((app) => {
                        if(app.url == this.teamworkapp_url)
                            exist = true;
                    });
                    if(exist){
                        this.$notify({
                            group: 'foo',
                            duration: 5000,
                            type: 'error',
                            speed: 1000,
                            title: 'Already exist!',
                            text: 'Can not add this account<br/>the same teamwork account url already exists'
                        });
                        this.connect_status = false;
                        return;
                    }
                    await axios.post('api/v1/teamwork/apps', {
                        url: this.teamworkapp_url,
                        token: this.teamworkapp_token
                    })
                    .then((response)=> {
                        this.teamworkApps.push(response.data);
                        this.teamworkapp_token = "";
                        this.teamworkapp_url = "";
                    })
                    .catch((error) => {
                        this.$notify({
                            group: 'foo',
                            duration: 5000,
                            type: 'error',
                            speed: 1000,
                            title: 'Connection Error',
                            text: 'Can not add this account<br/>Please check your connection and try again'
                        });
                    });
                }    
                this.connect_status = false;
            },
            //delete selected teamwork app
            delete_teamworkapp(app){
                var confirmresult = confirm("Are you really going to delete this teamwork account?");
                if(confirmresult){
                    axios.delete('api/v1/teamwork/apps/' + app.id).then(
                        () => {
                            console.log("delete successfully");
                            var index = this.teamworkApps.indexOf(app);
                            if (index > -1) {
                                this.teamworkApps.splice(index, 1);
                            }
                        }
                    )
                    .catch((error)=>{
                        this.$notify({
                            group: 'foo',
                            duration: 5000,
                            type: 'error',
                            speed: 1000,
                            title: 'Delete Error',
                            text: 'Can not delete this teamwork account!<br/> Please ensure the deletion info.'
                        });
                    });    
                }           
            },

            //edit teamwork app
            edit_teamworkapp(app){
                this.teamaddform_visible = true;
                this.teamworkapp_url = app.url;
                this.teamworkapp_token = app.token;
                this.curTeamworkappId = parseInt(app.id);
            },

            //teamwork accout sync
            sync_teamworkapp(app){
                this.sync_status = false;
                axios.get('api/v1/teamwork/apps/'+app.id+'/sync').then(
                    (url) => {
                        this.sync_url = url.data.url;
                        //if the server use https protocol then change the url to https://~
                        if(this.sync_url.indexOf("://") > 0){
                            if(this.sync_url.substring(0, this.sync_url.indexOf("://")) == "http"){
                                this.sync_url = this.sync_url.replace("http", "https");
                            }
                        }
                        this.sync_status = true;
                    }
                )
                .catch((error)=>{                    
                    this.sync_status = true;
                    this.$notify({
                            group: 'foo',
                            duration: 5000,
                            type: 'error',
                            speed: 1000,
                            title: 'Sync Error',
                            text: 'Can not Sync this teamwork account!<br/> Please ensure the connection info.'
                        });
                })
            },

            show_add_teamworkaccount(){
                this.teamaddform_visible = true;
            },

        }
    }
</script>