<template>
    <div>
        <router-link :to="{ name: 'addteamwork'}" class="custom-underline">
            Back
        </router-link>
        <h3>Project: {{ selectedJiraProject.name }}</h3>
        <br/>

        <div class="add-account" v-for="link in curlinks" v-bind:key="link.id" >
            <h4>{{ link.teamworkProject.name }} - {{selectedJiraProject.name}}</h4>         
            <label for="link" style="margin-top:15px;">Default Worklist : </label> <br/>     
            <form class="form-inline">  
                <input type="text" v-model="link.defaultTaskList" class="form-control">
                <a v-on:click="save_worklist(link)" class="btn btn-success">Save</a>
            </form><br/>
            <label for="link"> Migrated fields</label>  <a v-on:click="add_migratefield(link)" class="custom-underline"> Add</a><br/>

            <div v-for="(field, index) in link.calculatedFields.items" v-bind:key="field.id">
                <form class="form-inline"> 
                    <select v-model="field.targetField" class="form-control">
                        <option v-for="teamworkfield in teamwork_fields" :value="teamworkfield.name" v-bind:key="teamworkfield.id">
                            {{teamworkfield.name}}
                        </option>
                    </select>
                    <i class="fa fa-arrow-right" style="margin:0 10px;"></i>
                    <input type="text" v-model="field.formula" list="autolist" class="form-control" style="width:300px;"
                            :ref="link.id+'-'+index.toString()" @keyup="set_active(link.id+'-'+index.toString())" 
                            @mouseup="set_active(link.id+'-'+index.toString())">            
                    <a v-on:click="Remove_field(link, field)" class="custom-underline">Remove</a><br/><br/>
                </form>
            </div>

            <p class="err-message" v-if="link.err_status">{{link.err_message}}</p>
            Available jira fields:
            <a v-for="field in jira_fields" v-on:click="copy_text(link, field.name)" v-bind:key="field.id"
                class="custom-underline">{{field.name}}</a>
            <br/><br/>
            <a @click="remove_link(link)" class="custom-underline">Remove</a>
            <a v-on:click="save_calculatedFields(link)" class="btn btn-success" style="margin-left:50px;">Save</a>
        </div>    

        <br/>
        <div>
            <span class="text-inborder" v-if="con_status">LinkProject</span>
            <div class="add-account" v-if="con_status">      
                <label for="link">LinkProject:</label> <br/>     
                <form class="form-inline">                
                    <select  class="form-control" v-model="linkedTeamworkproject">
                        <option v-for="project in teamworkProjects" :value=project v-bind:key="project.id">
                            {{project.name}} - {{selectedJiraProject.name}}
                        </option>
                    </select>
                    <a class="btn3d btn btn-default" @click="link_teamworkProject(linkedTeamworkproject)" > Link </a>
                </form>  
                <p class="err-message" v-if="err_status">{{err_message}}</p>                      
            </div>  
             <img v-if="!con_status" src="/img/loading.gif">
        </div>   
        <datalist id="autolist">
            <option v-for="field in jira_fields" v-bind:key="field.id">{{field.name}}</option>
        </datalist>
    </div>
</template>

<script>

 export default {

    data() {
        return {
            selectedJiraProject:null,
            teamwork_fields:[],        
            jira_fields:[],
            teamworkProjects:[],
            curlinks:[],
            active_input:null,
            _selectStartPos:0,
            _selectEndPos:0,
            curFieldIds:[],
            con_status:false,
            linkedTeamworkproject:null,
        }
    },
    async mounted() {        
        this.selectedJiraProject = this.$route.params.selectedProject,
        this.err_status = false;
        this.err_message = "";
        

        //get all jira fields
        await axios.get('api/v1/projects/' + this.selectedJiraProject.id + '/fields').then(
            (fields) => {
                this.jira_fields = fields.data.items;
            }
        )
        .catch((err) =>{
            console.log(err);
        });

        //get all teamwork projects
        await axios.get('api/v1/teamwork/projects').then(
                (projects) => {                        
                    this.teamworkProjects = projects.data.items;
                    this.con_status = true;
                }
            )
            .catch((error)=>{
                this.$notify({
                        group: 'foo',
                        duration: 5000,
                        type: 'error',
                        speed: 1000,
                        title: 'Connect Error',
                        text: 'Can not connect with this teamwork projects!<br/> Please ensure the connection info.'
                    });
            });

        //get current link or create new link if not exist
        await axios.get('api/v1/projects/' + this.selectedJiraProject.id + '/links').then(
                (links) => {                    
                    links.data.items.forEach((link) => {
                            if(link.defaultTaskList == '')
                                link.defaultTaskList = "Backlog";
                            this.decode_fields(link.calculatedFields.items); // change formula to be available to see for users    
                            link.err_status = false;       
                            link.err_message = "";                                            
                    });
                    this.curlinks = links.data.items;                
                }
            )
            .catch((error)=>{
                this.$notify({
                        group: 'foo',
                        duration: 5000,
                        type: 'error',
                        speed: 1000,
                        title: 'No links',
                        text: 'There are no links for this project<br/> Please create new link.'
                    });
            });
        
        //get all of teamworkFields
        await axios.get('api/v1/links/1/teamworkFields').then(
                (fields) => {                    
                    this.teamwork_fields = fields.data.items;
                }
            )
            .catch((error)=>{
                this.$notify({
                        group: 'foo',
                        duration: 5000,
                        type: 'error',
                        speed: 1000,
                        title: 'Connect Error',
                        text: 'Can not get teamworkFields<br/> Please ensure the connection info.'
                    });
            });
    },

    methods: {        
        //change formulas of calculatedFields for display
        decode_fields(calculated_fields){
            var formula;
            var splitedFormula = [];
            var jira_field_name = "";
            var calculated_formula ;
            calculated_fields.map((item) => {
                calculated_formula = "";
                formula = item.formula;
                splitedFormula = formula.split("field(");
                splitedFormula.map((splitItem) => {
                    if(splitItem.indexOf(")") >= 0){
                       var tempid = splitItem.substring(0, splitItem.indexOf(")"));
                        this.jira_fields.forEach((item) => {
                            if(item.id == tempid){
                               jira_field_name = item.name;
                            }
                        });
                        splitItem = splitItem.replace(tempid + ")", jira_field_name);                        
                        calculated_formula += splitItem
                    }
                });
                item.formula = calculated_formula
            })
        }, 

        //encode formula for sending to server
        encode_fields(formula){
            this.jira_fields.forEach((jirafield)=>{
                if(formula.indexOf(jirafield.name) >= 0){
                    formula = formula.replace(new RegExp(jirafield.name, 'g'), "field(" + jirafield.id + ")");
                }
            });
            return formula;
        },

        //save worklist
        async save_worklist(link){
            await axios.put('api/v1/links/' + link.id, {
                    defaultTaskList:link.defaultTaskList
                }).then((res)=>{
                    this.$notify({
                        group: 'foo',
                        duration: 5000,
                        type: 'success',
                        speed: 1000,
                        title: 'Worklist saved',
                        text: 'This defaultTasklist of the link has been saved successfully.'
                    });
                })
                .catch((error)=>{
                    console.log(error);
                });
        },

        //save all calculatedFields of current link
        async save_calculatedFields(link){    
            var blankerror = false;
            link.calculatedFields.items.map((field) => {
                if(field.targetField == "" || field.formula == ""){
                    link.err_status = true;
                    link.err_message = "the targetField or formula can not be empty.";
                    blankerror = true;
                }
            });
            if(blankerror)return;
            link.err_status = false;
            await axios.get('api/v1/links/' + link.id).then(
                (response) => {
                    response.data.calculatedFields.items.map((field) => {
                        axios.delete('api/v1/links/' + link.id + '/calculatedFields/'+ field.id)
                        .then((res)=>{                    
                            console.log("delete successfullly!");
                        })
                        .catch((error)=>{    
                            console.log(error);                
                        });
                    });
                })
                .catch((error) => {
                    console.log(error);
                });

            await link.calculatedFields.items.map((field) =>{
                 axios.post('api/v1/links/' + link.id + '/calculatedFields', {
                     targetField:field.targetField,
                     formula:this.encode_fields(field.formula)
                 })
                 .then((field)=>{
                    console.log("field saved successfully!");
                    this.curFieldIds.push(field.data.id);
                })
                .catch((error)=>{
                    this.$notify({
                        group: 'foo',
                        duration: 5000,
                        type: 'error',
                        speed: 1000,
                        position:'top, left',
                        title: 'Formula error',
                        text: 'Syntax error in formular; please correct!'
                    });
                });
            });     
        },

        //add new calculatedField
        add_migratefield(link){
            var newField = {id:'', targetField:"", formula:""};
            link.calculatedFields.items.push(newField);
        },

        //remove a field 
        Remove_field(link, field){
            var index = link.calculatedFields.items.indexOf(field);
            if (index > -1) {
                link.calculatedFields.items.splice(index, 1);
            }
        },

        //set current editing input element
        set_active(inputId){
            this.active_input = this.$refs[inputId][0];
            this._selectStartPos = this.active_input.selectionStart;
            console.log(this._selectStartPos);
            this._selectEndPos = inputId.split("-")[0];
        },
        //while editing in input, when click the jirafield, copy it to editing input
        copy_text(link, fieldName){
            if(this.active_input == null || this._selectEndPos != link.id)return;
            var value = this.active_input.value;
            value = value.substring(0, this._selectStartPos) + fieldName + value.substring(this._selectStartPos);
            this.active_input.value = value;
            this.active_input.focus();
        },

        //remove a link
        //if user confirm to delete current link, then delete all calculatedFields of the link and delete itself.
        async remove_link(link){
            var confirmresult = confirm("Are you really going to delete this link?");
            if(confirmresult){
                await axios.get('api/v1/links/' + link.id).then(
                    (response) => {
                        response.data.calculatedFields.items.map((field) => {
                            axios.delete('api/v1/links/' + link.id + '/calculatedFields/'+ field.id)
                            .then((res)=>{                    
                                console.log("delete successfullly!");
                            })
                            .catch((error)=>{    
                                console.log(error);                
                            });
                        });
                    })
                    .catch((error) => {
                        console.log(error);
                    });
                await axios.delete('api/v1/links/' + link.id)
                    .then((res)=>{                    
                        console.log("delete successfullly!");
                        var index = this.curlinks.indexOf(link);
                        if (index > -1) {
                            this.curlinks.splice(index, 1);
                        }
                        this.$notify({
                            group: 'foo',
                            duration: 5000,
                            type: 'success',
                            speed: 1000,
                            position:'top, left',
                            title: 'Deleted success',
                            text: 'this link has been deleted successfully!'
                        });
                    })
                    .catch((err)=>{
                        this.$notify({
                            group: 'foo',
                            duration: 5000,
                            type: 'error',
                            speed: 1000,
                            position:'top, left',
                            title: 'Delete failed',
                            text: 'this link has not been deleted! please check the link again'
                        });
                    });   
            }
        },

        //create a new link
        async link_teamworkProject(teamworkProject) {
            var exist = false;
            this.curlinks.forEach((link) => {
                if(link.teamworkProject.id == teamworkProject.id){
                    exist = true;
                }                
            });
            if(exist){
                this.$notify({
                        group: 'foo',
                        duration: 5000,
                        type: 'error',
                        speed: 1000,
                        position:'top, left',
                        title: 'Already Exist',
                        text: 'can not add this link, the same link already exists'
                    });
                return;
            }
            //if no link then create a link
            await axios.post('/api/v1/projects/' + this.selectedJiraProject.id + '/links', {                                
                    id: teamworkProject.id,                                
                })
                .then((link) => {
                    link.data.items[0].default_worklist = "Backlog";
                    link.data.items[0].err_status = false;
                    link.data.items[0].err_message = "";
                    this.curlinks.push(link.data.items[0]);
                })
                .catch((error) => {
                    console.log(error);
                });                
        },
        

    },
}
</script>