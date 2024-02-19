<template>
    <title>Approvals View CoC - EMS</title>
    <Sidebar></Sidebar>
    <Navbar></Navbar>

    <div class="components">
        <div class="row utilities">
            <div class="col-6">
                <h2 class="my-1">
                    <span class="return" @click="returnPage">Approvals</span> > View > CoC
                </h2>
            </div>
            <template v-if="!isCoCLoading && coc_status !== ''">
                <div v-if="coc_status === 'Pending' && !makingCoCDecision" class="col-6" style="text-align: end;">
                    <ActionButton @click.prevent="acceptCoC" class="accept-button" style="margin-right: 2% !important; background-color: rgb(71, 182, 43);">Approve</ActionButton>
                    <ActionButton @click.prevent="openRejectModal" class="my-2">Reject</ActionButton>
                </div>

                <div v-if="makingCoCDecision" class="col-6" style="text-align: end;">
                    <h1 style="font-weight: 800; font-size: 2rem; color: rgb(15, 15, 15);">Processing..</h1>
                </div>

                <div v-if="coc_status !== 'Pending' && !makingCoCDecision" class="col-6" style="text-align: end;">
                    <h1 v-if="coc_status === 'Approved'" style="font-weight: 800; font-size: 2rem; color: rgb(71, 182, 43);">Approved</h1>
                    <h1 v-else-if="coc_status === 'Rejected'" style="font-weight: 800; font-size: 2rem; color: #B90321">Rejected</h1>
                </div>
            </template>
        </div>
        
        <div v-if="type === 'coc' && isCoCLoading">
            <div class="row">
                <div class="col-6">
                    <div class="note-timeline">
                        <h6 class="mx-3">Basic Information</h6>
                    </div>
                    <ImageSkeleton v-if="isCoCLoading" 
                            :loading="isCoCLoading" 
                            :itemCount="1" 
                            :borderRadius="'0px'"
                            :imageWidth="'38vw'" 
                            :imageHeight="'45vh'"
                            :containerMargin="'0% -0%'"
                            :itemMargin="'0em'">
                    </ImageSkeleton>

                    <div class="note-timeline">
                        <h6 class="mx-3">School Information</h6>
                    </div>

                    <ImageSkeleton v-if="isCoCLoading" 
                            :loading="isCoCLoading" 
                            :itemCount="1" 
                            :borderRadius="'0px'"
                            :imageWidth="'38vw'" 
                            :imageHeight="'45vh'"
                            :containerMargin="'0% -0%'"
                            :itemMargin="'0em'">
                    </ImageSkeleton>
                </div>

                <div class="col-6">
                    <div class="note-timeline">
                        <h6 class="mx-3">Attachments</h6>
                    </div>
                    <ImageSkeleton v-if="isCoCLoading" 
                            :loading="isCoCLoading" 
                            :itemCount="1" 
                            :borderRadius="'0px'"
                            :imageWidth="'38vw'" 
                            :imageHeight="'55vh'"
                            :containerMargin="'0% -0%'"
                            :itemMargin="'0em'">
                    </ImageSkeleton>
                </div>
            </div>
        </div>

        <div class="modal" v-if="isRejectModalOpen">
            <div class="modal-content">
                <div style="text-align: center;">
                    <h2>Reject CoC</h2>
                </div>
                <p>Please add a reason for rejecting the CoC:
                    <ToolTip class="mx-1">
                        <slot>
                            This will be sent to the corresponding email of the student.
                        </slot>
                    </ToolTip>
                </p>
                
                <textarea class="form-control text-area-input" style="height: 250px;" type="text" name="name" v-model="rejectReason"></textarea>

                <div class="button" style="margin-top: 20px;">
                    <ActionButton style="margin-right: 15px;" @click.prevent="rejectCoC" :disabled="makingCoCDecision">{{ confirmRejectTextButton }}</ActionButton>
                    <ActionButton @click.prevent="isRejectModalOpen = false" :disabled="makingCoCDecision">Cancel</ActionButton>
                </div>
            </div>
        </div>

        <div v-if="type === 'coc' && !isCoCLoading">
            <div class="row">
                <div class="col-6">
                    <div class="note-timeline">
                        <h6 class="mx-3">Basic Information</h6>
                    </div>
                    <div class="box">
                        <div class="first-info">
                            <h6 class="form-label" for="name">Student Number</h6>
                            <input class="form-control" type="text" name="name" v-model="CoCData.StudentNumber" :disabled="true">
                        </div>
                        
                        <div class="margin">
                            <div class="row">
                                <div class="col-4">
                                    <h6 class="form-label" for="type">First Name</h6>
                                    <input class="form-control" type="text" name="name" v-model="CoCData.Student.FirstName" :disabled="true">
                                </div>

                                <div class="col-4">
                                    <h6 class="form-label" for="type">Middle Name</h6>
                                    <input class="form-control" type="text" name="name" v-model="CoCData.Student.MiddleName" :disabled="true">
                                </div>

                                <div class="col-4">
                                    <h6 class="form-label" for="type">Last Name</h6>
                                    <input class="form-control" type="text" name="name" v-model="CoCData.Student.LastName" :disabled="true">
                                </div>
                            </div>
                        </div>

                        <div class="margin">
                            <h6 class="form-label" for="type">Motto</h6>
                            <textarea class="form-control text-area-input" type="text" name="name" v-model="CoCData.Motto" :disabled="true"></textarea>
                        </div>

                        <div class="margin">
                            <h6 class="form-label" for="type">Email Address</h6>
                            <input class="form-control" type="text" name="name" v-model="CoCData.Student.Email" :disabled="true">
                        </div>
                    </div>

                    <div class="note-timeline">
                        <h6 class="mx-3">Political Information</h6>
                    </div>

                    <div class="box">
                        <div class="first-info">
                            <h6 class="form-label" for="type">Election Title Applied</h6>
                            <input class="form-control" type="text" name="name" v-model="CoCData.ElectionName" :disabled="true">
                        </div>

                        <div class="margin">
                            <h6 class="form-label" for="type">Organization</h6>
                            <input class="form-control" type="text" name="name" v-model="CoCData.StudentOrganizationName" :disabled="true">
                        </div>

                        <div class="margin">
                            <h6 class="form-label" for="type">Political Affiliation</h6>
                            <input class="form-control" type="text" name="name" v-model="CoCData.PoliticalAffiliation" :disabled="true">
                        </div>

                        <div v-if="CoCData.PoliticalAffiliation !== 'Independent'" class="margin">
                            <h6 class="form-label" for="type">Party List</h6>
                            <input class="form-control" type="text" name="name" v-model="CoCData.PartyListName" :disabled="true">
                        </div>

                        <div class="margin">
                            <h6 class="form-label" for="type">Position Selected</h6>
                            <input class="form-control" type="text" name="name" v-model="CoCData.SelectedPositionName" :disabled="true">
                        </div>       

                        <div class="margin">
                            <h6 class="form-label" for="type">Platforms</h6>
                            <textarea class="form-control text-area-input" type="text" name="name" v-model="CoCData.Platform" :disabled="true"></textarea>
                        </div>
                    </div>

                    <div class="note-timeline">
                        <h6 class="mx-3">School Information</h6>
                    </div>

                    <div class="box">
                        <div class="row">
                            <div class="col-6">
                                <div class="first-info">
                                    <h6 class="form-label" for="type">Course</h6>
                                    <input class="form-control" type="text" name="name" v-model="CoCData.Student.CourseCode" :disabled="true">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="first-info">
                                    <h6 class="form-label" for="type">Current Semester Enrolled</h6>
                                    <input class="form-control" type="text" name="name" v-model="getSemester" :disabled="true">
                                </div>
                            </div>
                        </div>

                        <div class="row my-2">
                            <div class="col-6">
                                <div class="margin">
                                    <h6 class="form-label" for="type">Year Level</h6>
                                    <input class="form-control" type="text" name="name" v-model="yearLevel" :disabled="true">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="margin">
                                    <h6 class="form-label" for="type">Section</h6>
                                    <input class="form-control" type="text" name="name" v-model="CoCData.Student.Section" :disabled="true">
                                </div>
                            </div>
                        </div>            
                    </div>
                </div>
                
                <div class="col-6">
                    <template v-if="CoCData.Student.ClassGrade > 3 && !isCoCLoading">
                        <div class="note-timeline">
                            <h6 class="mx-3">Grades Information (According to SPS)</h6>
                        </div>
                        <div class="box" style="height: 85px;">
                            <div class="row">
                                <div class="col-1">
                                    <i class="fas fa-exclamation-triangle" style="color: #FFD43B; margin-top: -27%;"></i>
                                </div>
                                <div class="col-11">
                                    <h6>This student has an average grade of {{ CoCData.Student.ClassGrade }}, which is considered failed.</h6>
                                </div>
                            </div>
                        </div>
                    </template>

                    <div class="note-timeline">
                        <h6 class="mx-3">Attachments (Click to view in fullsize)</h6>

                    </div>
                    <div class="box">
                        <h6>Display Photo</h6>

                        <div class="image">
                            <img v-if="!isCoCLoading" :src="CoCData.DisplayPhoto" @click="viewImage(CoCData.DisplayPhoto)" class="pic" draggable="false">
                            <img v-else src="" class="pic" draggable="false">
                        </div>

                        <hr class="my-3">
                        <h6>Certification of Grades</h6>

                        <div v-if="!isCoCLoading">
                            <img :src="CoCData.CertificationOfGrades" alt="Certification of Grades" @click="viewImage(CoCData.CertificationOfGrades)" 
                            style="width: 500px; height: auto; margin-left: 11.5%; margin-top: 1%; cursor: pointer;" >
                        </div>
                        
                    </div>
                </div>
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
    import ToolTip from '../../Shared/Tooltip.vue';

    import { useQuery, useMutation, useQueryClient  } from "@tanstack/vue-query";
    import axios from 'axios';

    export default {
        setup(props) {
            const type = ref(props.type);
            const id = ref(Number(props.id));

            const coc_status = ref('');
            const isRejectModalOpen = ref(false);
            const rejectReason = ref('');

            const makingCoCDecision = ref(false);

            const fetchCoCData = async () => {
                const response = await axios.get(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/coc/${id.value}`);
                console.log(`CoC with id ${id.value} fetched successfully. Duration: ${response.duration}`)

                return response.data.coc;
            }

            const { data: CoCData,
                    isLoading: isCoCLoading,
                    isError: isCoCError,
                    error: CoCError } = 
                    useQuery({
                        queryKey: [`fetchCoCData-${id.value}`],
                        queryFn: fetchCoCData,
                    })

            watchEffect(() => {
                if (!isCoCLoading.value) {
                    coc_status.value = CoCData.value.Status;
                }
            })

            return {
                type,
                id,
                coc_status,
                isRejectModalOpen,
                rejectReason,

                makingCoCDecision,

                CoCData,
                isCoCLoading,
                isCoCError,
                CoCError,
            }
        },
        components: { Navbar, Sidebar, ActionButton, BaseContainer, BaseTable, ActionButton, ImageSkeleton, ToolTip },
        props: {
            type: '',
            id: '',
        },
        computed:{
            formattedBirthDate() {
                let options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(this.CoCData.Student.BirthDate).toLocaleDateString(undefined, options);
            },
            yearLevel() {
                if (this.CoCData.Student.Year === 1) {
                    return '1st Year';
                } 
                else if (this.CoCData.Student.Year === 2) {
                    return '2nd Year';
                } 
                else if (this.CoCData.Student.Year === 3) {
                    return '3rd Year';
                } 
                else if (this.CoCData.Student.Year === 4) {
                    return '4th Year';
                } 
            },
            getSemester(){
                if (this.CoCData.Student.Semester === 1) {
                    return '1st Semester';
                } 
                else if (this.CoCData.Student.Semester === 2) {
                    return '2nd Semester';
                } 
                else if (this.CoCData.Student.Semester === 3) {
                    return '3rd Semester';
                } 
            },
            confirmRejectTextButton(){
                return this.makingCoCDecision ? 'Processing..' : 'Confirm';
            }
        },
        methods: {
            returnPage() {
                router.visit('/comelec/approvals');
            },
            formatDate(date) {
                let options = { year: 'numeric', month: 'long', day: 'numeric' };
                return new Date(date).toLocaleDateString(undefined, options);
            },
            viewImage(image) {
                window.open(image, '_blank');
            },
            getEmbedUrl(url) {
                let videoId = url.split('v=')[1];
                
                return 'https://www.youtube.com/embed/' + videoId;
            },
            acceptCoC() {
                const confirm = window.confirm('Are you sure you want to accept this CoC?');
                if (!confirm) return;

                if (this.makingCoCDecision) {
                    return;
                }

                this.makingCoCDecision = true;
                axios.put(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/coc/${this.id}/accept`)
                    .then((response) => {
                        console.log(response);
                        this.coc_status = 'Approved';
                    })
                    .catch((error) => {
                        console.log(error);
                    })
                    .finally(() => {
                        this.makingCoCDecision = false;
                    })
            },
            openRejectModal() {
                this.isRejectModalOpen = true;
            },
            rejectCoC() {
                /*const confirm = window.confirm('Are you sure you want to reject this CoC?');
                if (!confirm) return; */

                if (this.rejectReason === '') {
                    alert('Please add a reason for rejecting the CoC.');
                    return;
                }

                if (this.makingCoCDecision) {
                    return;
                }

                // make form data for the reject reason
                const formData = new FormData();
                formData.append('reject_reason', this.rejectReason);

                this.makingCoCDecision = true;
                axios.put(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/coc/${this.id}/reject`, formData, {
                })
                .then((response) => {
                    console.log(response);
                    this.coc_status = 'Rejected';
                    this.rejectReason = '';
                })
                .catch((error) => {
                    console.log(error);
                })
                .finally(() => {
                    this.makingCoCDecision = false;
                    this.isRejectModalOpen = false;
                })
            },
        },
    }
</script>

<style scoped>
    .fas.fa-exclamation-triangle {
        font-size: 35px;
    }

    .modal {
        display: block; /* Hidden by default */
        position: fixed; /* Stay in place */
        z-index: 1; /* Sit on top */
        padding-top: 100px; /* Location of the box */
        left: 0;
        top: 0;
        width: 100%; /* Full width */
        height: 100%; /* Full height */
        background-color: rgb(0,0,0); /* Fallback color */
        background-color: rgba(0,0,0,0.4); /* Black w/ opacity */
    }

        /* Modal Content */
    .modal-content {
        background-color: #fefefe;
        margin: auto;
        padding: 20px;
        border: 1px solid #888;
        width: 40%;
        max-height: 70%;
        overflow: auto;
    }
    .utilities{
        margin-bottom: 1%;
    }

    .accept-button:disabled{
        background-color: #cccccc !important;
    }

    .return{
        color: #B90321;
        cursor: pointer;
    }

    .return:hover{
        text-decoration: underline;
    }

    .components{
        margin-left: 18%;
        margin-top: 2%;
        font-family: 'Inter', sans-serif;
        margin-right: 3.2%;
    }

    .components h2{
        font-weight: 800;
        font-size: 30px;
        margin-bottom: 1.5%;
    }
    
    .form-control, .form-select {
        border: 1px solid rgba(40, 40, 40, 0.25);
    }

    .margin{
        margin-right: -1%;
        margin-left: -1%;
    }

    .button{
        margin-top: 5%;
    }

    .save{
        text-align: end;
    }

    .note{
        margin-top: 1.5%;
        background-color: #c2eba7;
        box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.14), 0 6px 20px 0 rgba(0, 0, 0, 0.08);
        padding: 2%;
    }

    .note h6{
        margin-top: 10px;
    }

    .note-timeline{
        margin-top: 1.5%;
        background-color: #eec865;
        padding: 1.5%;
    }

    .note-timeline h6{
        margin-top: 10px;
    }

    .box{
        background-color: white;
        box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.14), 0 6px 20px 0 rgba(0, 0, 0, 0.08);
        padding: 4.5%;
        margin-bottom: 3%;
    }

    .upper-box{
        padding-bottom: 5%;
    }

    .position-box{
        background-color: white;
        box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.14), 0 6px 20px 0 rgba(0, 0, 0, 0.08);
        padding: 3% 3% 2% 3%;
    }

    .first-info{
        margin-top: -2%;
    }

    .text-area-input{
        min-height: 4rem;
        height: fit-content;
        resize: none;
    }

    .margin{
        margin-top: 3%;
        margin-left: 0%;
    }

    .list{
        margin-top: 3.5%;
    }

    .head{
        text-align: center;
    }

    .position-btn{
        text-align: end;
    }

    .election-btn{
        text-align: end;
    }

    .new-btn{
        margin-top: .5%;
    }

    .remove-btn {
        margin-left: -1%;
        margin-top: -10%;
        border: transparent;
        border-radius: 10px;
        background-color: transparent;
        color: #CC3300;
    }

    .remove-btn:hover{
        color: #B90321;
    }

    .remove-btn:disabled{
        color: #cccccc;
    }

    .reusable-btn {
        margin-top: -10% !important;
        border: transparent;
        border-radius: 10px;
        background-color: transparent;
        color: #00ae0c;
    }

    .reusable-btn:hover{
        color: rgb(12, 194, 2);
    }

    .reusable-btn:disabled{
        color: #cccccc !important;
    }

    .cancel{
        color: black;
        background-color: #FDD5D5;
        margin-right: 2%;
    }

    .cancel:hover{
        background-color: #ffcfcf;
    }

    .image{
        text-align: center;
    }

    .pic{
        width: 155px;
        height: 155px;
        object-fit: cover;
        border-radius: 100px;
        outline: 1px solid #bbbb;
        cursor: pointer;
    }
</style>