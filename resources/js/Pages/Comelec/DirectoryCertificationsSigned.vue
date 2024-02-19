<template>
    <title>Directory Certifications - EMS</title>
    <Sidebar></Sidebar>
    <Navbar></Navbar>

    <div class="components">
        <div class="header">
            <h2 class="my-1 page-title">
                <span class="return" @click="returnDirectory">Directory</span> >
                <span class="return" @click="returnPage">Certifications</span> > Signed
            </h2>
        </div>

        <div class="row">
            <div class="col-5 my-2" style="padding-right: 2%;">
                <div class="note">
                    <h6>Upload PDF certificate files.</h6>
                </div>
                <div class="box">
                    <label for="file-upload" class="custom-file-upload" :class="{ 'disabled': is_loading_attachments || saving || updating }">
                                Select File
                    </label>
                    <input id="file-upload" type="file" style="display: none;" @change="onFileChange" :disabled="is_loading_attachments || saving || updating" multiple/>

                    <DragAndDrop 
                        class="drag-and-drop my-1"
                        v-model="selectedFiles" 
                        :fileSize="file_size"
                        :acceptedFileTypes="extensions"
                        :notAcceptedMessage="notAcceptedMessage"
                        :isLoadingAttachments="is_loading_attachments"
                        :saving="saving"
                        :updating="updating">
                    </DragAndDrop>

                    <ActionButton @click.prevent="submitAttachmentFile" :disabled="saving" class="mt-4 upload">{{ uploadButtonText }}</ActionButton>
                </div>
            </div>
            
            <div class="col-7">
            <BaseContainer class="item-container" :height="'auto'" :maxHeight="'75vh'">
                <BaseTable class="item-table" 
                        :columns="['ID', 'Certification Title', 'Date Uploaded', 'Actions']" 
                        :tableHeight="'auto'"
                        :columnWidths="columnWidths"
                        :maxTableHeight="'69vh'">
                    <tr v-for="(certification, index) in certificationsData" :key="index">
                        <td :style="{ width: columnWidths[0] }" class="my-cell">{{ index + 1 }}</td>
                        <td :style="{ width: columnWidths[1] }" class="my-cell ellipsis">{{ certification.CertificationTitle }}</td>
                        <td :style="{ width: columnWidths[2] }" class="my-cell">{{ toDate(certification.DateUploaded) }}</td>
                        <td :style="{ width: columnWidths[3] }" class="my-cell">
                            <ActionButton 
                                @click="previewCertificationSigned(certification)" class="preview-button">
                                <i class="fas fa-eye"></i>
                            </ActionButton>
                            <ActionButton 
                                @click="downloadCertificationSigned(certification)" class="download-button mx-2">
                                <i class="fas fa-download"></i>
                            </ActionButton>
                            <ActionButton 
                                @click="deleteCertificationSigned(certification)" class="delete-button">
                                <i class="fas fa-trash-alt"></i>
                            </ActionButton>
                        </td>
                    </tr>
                </BaseTable>
            </BaseContainer>
            </div>
        </div>

    </div>
</template>

<script>
    import { router } from '@inertiajs/vue3'
    import { ref, watch, watchEffect } from 'vue';

    import Navbar from '../../Shared/Navbar.vue';
    import Sidebar from '../../Shared/Sidebar.vue';
    import ActionButton from '../../Shared/ActionButton.vue';
    import BaseContainer from '../../Shared/BaseContainer.vue';
    import BaseTable from '../../Shared/BaseTable.vue';
    import ImageSkeleton from '../../Skeletons/ImageSkeleton.vue';
    import DragAndDrop from '../../Shared/DragAndDrop.vue';

    import { useQuery } from "@tanstack/vue-query";
    import axios from 'axios';

    export default {
        setup() {
            const selectedFiles = ref([]);
            const file_size = ref(10); // mega bytes
            const saving = ref(false);
            const updating = ref(false);
            const is_loading_attachments = ref(false);
            const notAcceptedMessage = ref('please upload a pdf file.');
            const extensions = ref('application/pdf') 

            const columnWidths = ['10%', '35%', '30%', '30%'];

            const fetchCertificationsSigned = async () => {
                const response = await axios.get(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/certification/signed/all`)
                console.log(`Certifications successfully fetched. Duration: ${response.duration}`)

                return response.data.certifications_signed;
            };

            const { data: certificationsData, isLoading, isSuccess, isError, refetch } = 
                useQuery({
                    queryKey: ['fetchCertificationsSigned'],
                    queryFn: fetchCertificationsSigned,
                });

            return {
                selectedFiles,
                file_size,
                saving,
                updating,
                is_loading_attachments,
                notAcceptedMessage,
                extensions,
                columnWidths,

                certificationsData,
                isLoading,
                isSuccess,
                isError,
                refetch,
            }
        },
        components: {
            Navbar,
            Sidebar,
            ActionButton,
            BaseContainer,
            BaseTable,
            ImageSkeleton,
            DragAndDrop,
        },
        computed: {
            uploadButtonText() {
                return this.saving || this.updating ? 'Uploading..' : 'Upload file';
            }
        },
        methods: {
            returnDirectory(){
                router.visit('/comelec/directory');
            },
            returnPage(){
                router.visit('/comelec/directory/certifications');
            },
            createCertification(){
                router.visit('/comelec/directory/certifications/create');
            },
            toDate(date){
                return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
            },
            previewCertificationSigned(certification){
                axios.get(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/certification/signed/preview/${certification.CertificationsSignedId}`)
                .then((response) => {
                    window.open(response.data.pdf, '_blank');
                })
            },
            downloadCertificationSigned(certification){
                axios({
                    url: `${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/certification/signed/download/${certification.CertificationsSignedId}`,
                    method: 'GET',
                    responseType: 'blob', // important
                })
                .then((response) => {
                    const url = window.URL.createObjectURL(new Blob([response.data]));
                    const link = document.createElement('a');
                    
                    // Use the student number as the filename
                    link.href = url;
                    link.setAttribute('download', `${certification.CertificationTitle}.pdf`); // replace with actual student number attribute

                    document.body.appendChild(link);
                    link.click();
                })
            },
            deleteCertificationSigned(certification){
                if (confirm('Are you sure you want to delete this certification?')) {
                    axios.delete(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/certification/signed/delete/${certification.CertificationsSignedId}`)
                    .then((response) => {
                        alert('Certification deleted successfully.');
                        this.refetch();
                    })
                }
            },
            addFiles(files) {
                // Add the files to the list of files
                for (let i = 0; i < files.length; i++) {
                    let file = files[i];

                    if (file.size > this.file_size * 1024 * 1024) {
                        alert(file.name + ' is larger than ' + String(this.file_size) + ' MB, please upload a smaller file');
                        continue;
                    }

                    const extensions = this.extensions;
                    let acceptedTypes = extensions.split(',');

                    if (!acceptedTypes.includes(file.type)) {
                        alert(file.name + ' is not an accepted file type, ' + this.notAcceptedMessage);
                        continue;
                    }

                    // Create a new object URL for the file
                    let url = URL.createObjectURL(file);

                    // Check if the file is already in the list of files
                    // If it is, then do not add it again
                    if (!this.selectedFiles.some(existingFile => existingFile.name === file.name)) {
                        this.selectedFiles.push({ file:file, 
                                                name: file.name, 
                                                url: url
                                            });
                    }
                }
            },
            onFileChange(e) {
                let files = e.target.files || e.dataTransfer.files;

                if (files) {
                    this.addFiles(files);
                }

                // Clear the input value
                e.target.value = null;
            },
            submitAttachmentFile() {
                if (this.selectedFiles.length == 0) {
                    alert('Please select a file to upload.');
                    return;
                }

                let formData = new FormData();

                for (let i = 0; i < this.selectedFiles.length; i++) {
                    formData.append('files', this.selectedFiles[i].file);
                }

                this.saving = true;

                axios.post(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/certification/signed/upload`, formData, {
                })
                .then(response => {
                    alert('File uploaded successfully.');
                    this.refetch();
                }).catch(error => {
                    console.log(error);
                })
                .finally(() => {
                    this.saving = false;
                    this.selectedFiles = [];
                });
            },
        }
    }
</script>

<style scoped>
   .components{
        margin-left: 18%;
        margin-top: 2%;
        font-family: 'Inter', sans-serif;
        margin-right: 3%;
    }

    .return{
        color: #B90321;
        cursor: pointer;
    }

    .return:hover{
        text-decoration: underline;
    }

    .header{
        display: flex;
        align-items: center;
        margin: 0% -1%;
        justify-content: space-between;
    }

    .page-title{
        font-weight: 900;
        font-size: 28px;
        margin: 0%;
    }

    .list{
        margin-top: 1.5%;
        background-color: white;
        margin: 1.5% -1% 0% -1%;
        padding: 30px 30px 15px 30px;
        border-radius: 7px;
        max-height: 550px;
        box-shadow: 0px 3px 5px rgba(167, 165, 165, 0.5);
    }

    .preview-button{
        padding: 2% 8%;
        border-radius: 10px;
        background-color: #38a31b;
    }
    .download-button{
        padding: 2% 8%;
        border-radius: 10px;
        background-color: #136ac2;
    }

    .preview-button:hover{
        background-color: #2e8a14;
    }

    .download-button:hover{
        background-color: #0f4e9c;
    }

    .delete-button{
        padding: 2% 8%;
        border-radius: 10px;
        background-color: #B90321;
    }

    .delete-button:hover{
        background-color: #8a0220;
    }

    .note{
        margin-top: 1.5%;
        background-color: #FDD5D5;
        box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.14), 0 6px 20px 0 rgba(0, 0, 0, 0.08);
        padding: 2%;
    }

    .note h6{
        margin-top: 10px;
        margin-left: 10px;
    }

    .box{
        background-color: white;
        box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.14), 0 6px 20px 0 rgba(0, 0, 0, 0.08);
        padding: 3%;
    }

    .clear{
        width: 100%;
    }

    .insert{
        width: 100%;
    }

    .upload{
        width: 100%;
    }

    .upload:disabled{
        background-color: #cccccc;
    }

    .custom-file-upload {
        margin-bottom: 2.5%;
        padding: 7px;
        width: 100%;
        font-size: 100%;
        border: 1px solid #ccc;
        border-radius: 8px;
        display: inline-block;
        cursor: pointer;
        text-align: center;
    }

    .custom-file-upload:hover{
        background-color: #f4f4f4;
    }

    .custom-file-upload.disabled {
        background-color: #E9ECEF;
        cursor: default;
    }

    .drag-and-drop{
        height: 250px;
    }

    .ellipsis {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
</style>