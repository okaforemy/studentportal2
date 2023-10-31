<template>
    <div>
        <div class="col-md-8 pt-4">
            <form enctype="multipart/form-data">
                <label for="">Please select a word document</label>
                <input type="file" class="form-control" @change="setUploadFile" accept=".docx">
                <button type="button" class="btn btn-primary t-3" @click.prevent="upload">Upload</button>
            </form>

        </div>
    </div>
</template>

<script>
export default {
    data(){
        return{
            file:null,
        }
    },
    methods:{
        setUploadFile(event){
            this.file = event.target.files[0];
        },

        upload(){
            let formData = new FormData();
            formData.append('file', this.file);

            axios.post('/upload-question', formData, {
                headers: {
                'Content-Type': 'multipart/form-data',
                },
            })
            .then(response => {
                console.log(response.data.message);
            })
            .catch(error => {
                console.error(error);
            });
        }
    }
}
</script>