<template>
    <title>Elections Create - EMS</title>
    <Sidebar></Sidebar>
    <Navbar></Navbar>

    <div class="components">
        <div class="row utilities">
            <div class="col-5">
                <h2 class="my-1">
                    <span class="return" @click="returnPage">Elections</span> > Create Election
                </h2>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-6">
                <div class="note">
                    <h6>Set election title and organization.</h6>
                </div>
                <div class="box upper-box">
                    <label class="form-label" for="name">Election Title</label>
                    <input class="form-control margin" type="text" name="name" v-model="election_name_input">
                    
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label" for="type">Election Student Organization</label>
                            <input type="hidden" name="election-type">
                            <select v-model="election_type_input" class="form-select" aria-label="Default select example">
                                <option :value="userStore.student_organization_id" disabled selected>{{ userStore.student_organization_name }}</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label" for="type">Course Requirements</label>
                            <input v-model="election_course_requirements" class="form-control" type="text" name="name" readonly disabled>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="note">
                    <h6>Set the current school year and semester for this election.</h6>
                </div>
                <div class="box upper-box">
                    <label class="form-label" for="sy">School Year</label>
                    <select class="form-select margin" name="SY" v-model="election_school_year_input">
                        <option value="" disabled hidden selected>Select school year</option>
                        <option v-for="year in nextFiveYears" :key="year" :value="year">{{ year }}</option>
                    </select>

                    <label class="form-label" for="sem">Semester</label>
                    <select class="form-select" name="sem" v-model="election_semester_input">
                        <option value="" disabled hidden selected>Select semester</option>
                        <option value="1st Semester">1st Semester</option>
                        <option value="2nd Semester">2nd Semester</option>
                    </select>
                </div>
            </div>
        </div>
        
        <div>
            <div class="note-timeline">
                <h6>Set a timeline for the whole election.</h6>
            </div>
            <div class="box">
                <div class="row">
                    <label for="election-period" class="col-3 col-form-label">Election Period</label>
                    <div class="col-5">
                        <label class="form-label" for="e-start">Election Start</label>
                        <input type="datetime-local" class="form-control margin" v-model="election_start_input">

                        <label class="form-label" for="e-end">Election End</label>
                        <input type="datetime-local" class="form-control margin" v-model="election_end_input" :min="election_start_input">
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    <label for="filing-period" class="col-3 col-form-label">Filing of COC/Partylist Period</label>
                    <div class="col-5">
                        <label class="form-label" for="f-start">Filing Start</label>
                        <input type="datetime-local" class="form-control margin" v-model="election_filing_coc_start_input">

                        <label class="form-label" for="f-end">Filing End</label>
                        <input type="datetime-local" class="form-control margin" v-model="election_filing_coc_end_input" :min="election_filing_coc_start_input">
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    <label for="filing-period" class="col-3 col-form-label">Campaign Period</label>
                    <div class="col-5">
                        <label class="form-label" for="c-start">Campaign Start</label>
                        <input type="datetime-local" class="form-control margin" v-model="election_campaign_start_input" :min="election_filing_coc_end_input">

                        <label class="form-label" for="c-end">Campaign End</label>
                        <input type="datetime-local" class="form-control margin" v-model="election_campaign_end_input" :min="election_campaign_start_input">
                    </div>
                </div>

                <hr class="my-4">

                <div class="row">
                    <label for="filing-period" class="col-3 col-form-label">Voting Period</label>
                    <div class="col-5">
                        <label class="form-label" for="v-start">Voting Start</label>
                        <input type="datetime-local" class="form-control margin" v-model="election_voting_start_input" :min="election_campaign_end_input">

                        <label class="form-label" for="v-end">Voting End</label>
                        <input type="datetime-local" class="form-control margin" v-model="election_voting_end_input" :min="election_voting_start_input">
                    </div>
                </div>
            </div>
        </div>

        <div>
            <div class="note-timeline">
                <div class="row">
                    <div class="col-6 my-1">
                        <h6>Create positions for the election and set the quantity for each position.</h6>
                    </div>
                    <div class="col-6">
                        <div class="position-btn">
                            <ActionButton class="new-btn" @click.prevent="addNewPosition" :disabled="!isMostRecentPositionFilled">Add new position</ActionButton>
                        </div>
                    </div>
                </div>
            </div>
            <div class="position-box mb-4" v-for="(position, index) in position_list" :key="index">
                <div class="row g-4">
                    <div class="col">
                        <label class="form-label" for="position-name">Position Name</label>
                        <div class="row">
                            <div class="col-4">
                                <select v-model="position.value" @change="onPositionListSelect" class="form-select margin" aria-label="Position selection">
                                    <option value="" disabled hidden selected>Org Positions</option>
                                    <option value="New" style="color: #00ae0c;">Create new</option>
                                    <option v-for="saved_position in position_saved_selection" 
                                            :value="saved_position.value"
                                            :disabled="saved_position.is_already_selected">
                                        {{ saved_position.name }}
                                    </option>
                                </select>
                            </div>
                            <div class="col-8">
                                <input type="text" class="form-control position-name margin" 
                                    v-model="position.name" 
                                    :disabled="position.value !== 'New'">
                            </div>
                        </div>

                        <label class="form-label" for="position-quantity">Position Quantity</label>
                        <ToolTip class="mx-2">
                            <slot>
                                How many can run and win for this position.
                            </slot>
                        </ToolTip>
                        <input type="number" min="1" class="form-control position-quantity margin" v-model.number="position.quantity">

                        <div class="row button">
                            <div class="col-6">
                                <button class="remove-btn" 
                                        v-if="index !== 0"
                                        @click.prevent="removePosition(index)">Remove position</button>
                            </div>
                            <div class="col-6 save">
                                <button hidden :disabled="true || position.name === ''" class="reusable-btn" :style="{ color: position.is_re_usable ? '#00ae0c' : '#B90321' }"
                                        @click.prevent="makePositionReusableOrNot(index)">{{ position.is_re_usable ? 'Make this position reusable' : 'Make this position non-reusable' }}
                                </button>
                            </div>
                        </div>
                    </div>
                    <div class="col">
                        
                    </div>
                </div>
            </div>
        </div>
        <div>
            <div class="election-btn mb-4">
                <!-- <ActionButton class="cancel" @click.prevent="returnPage" :disabled="submitting">Cancel</ActionButton> -->
                <ActionButton class="create" @click.prevent="submit" :disabled="submitting">{{ createElectionButtonText }}</ActionButton>
            </div>
        </div>
    </div>
</template>

<script>
    import { useUserStore } from '../../Stores/UserStore';
    import { router } from '@inertiajs/vue3'
    import { ref, watch, computed } from 'vue';
    import { useQuery } from "@tanstack/vue-query";

    import Navbar from '../../Shared/Navbar.vue';
    import Sidebar from '../../Shared/Sidebar.vue';
    import ActionButton from '../../Shared/ActionButton.vue';
    import SearchBarAndFilter from '../../Shared/SearchBarAndFilter.vue';
    import BaseContainer from '../../Shared/BaseContainer.vue';
    import BaseTable from '../../Shared/BaseTable.vue';
    import ToolTip from '../../Shared/Tooltip.vue'

    import axios from 'axios';

    export default{
        setup(props) {
            const userStore = useUserStore();
            const submitting = ref(false);
            const createdByStudentNumber = userStore.student_number;

            const election_name_input = ref('');
            const election_type_input = ref(userStore.student_organization_id);
            const election_course_requirements = ref('');
            const election_school_year_input = ref('');
            const election_semester_input = ref('');
            const election_start_input = ref('');
            const election_end_input = ref('');
            const election_filing_coc_start_input = ref('');
            const election_filing_coc_end_input = ref('');
            const election_campaign_start_input = ref('');
            const election_campaign_end_input = ref('');
            const election_voting_start_input = ref('');
            const election_voting_end_input = ref('');
            const election_appeal_start_input = ref('');
            const election_appeal_end_input = ref('');

            const position_count = ref(1); // The number of positions
            const position_saved_selection = ref([]); // The list of saved positions in the DB
            const position_list = ref([{ // The list of positions created by the user
                count: position_count,
                name: '',
                value: '',
                quantity: 1,
                is_re_usable: true,
            }]);

            const watchPosition = (position) => {
                watch(() => position.value, (newVal) => {
                    if (newVal === 'New') {
                        position.name = '';
                    } 
                    else {
                        position.name = newVal;
                    }
                });

                // Determine if the position is reusable or not in initial load
                watch(() => position.name, (newName) => {
                    axios.get(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/election/position/reusable/all`)
                    .then((response) => {
                        const positions = response.data.positions;
                        const positionExists = positions.some((pos) => pos.PositionName === newName);

                        // Determine if can make reusable or remove reusability
                        if (positionExists) {
                            position.is_re_usable = false;
                        } 
                        else {
                            position.is_re_usable = true;
                        }
                    })
                    .catch((error) => {
                        console.log(error);
                    });
                });
            };

            // Watch for changes in the position list value
            position_list.value.forEach(watchPosition);

            // Get all saved positions in DB and add them to the position saved selection list
            axios.get(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/election/position/reusable/all`)
                .then((response) => {
                    const positions = response.data.positions;
                    
                    positions.forEach((pos) => {
                        const savedPositionsSelection = {
                            name: pos.PositionName,
                            value: pos.PositionName,
                            is_already_selected: false,
                        };
                        position_saved_selection.value.push(savedPositionsSelection);
                    });
                })
                .catch((error) => {
                    console.log(error);
                });

            // Button (add new position) logic handler
            // Return true if the most recent position is filled, if true enable the add new position button
            const isMostRecentPositionFilled = computed(() => {
                const mostRecentPosition = position_list.value[position_list.value.length - 1];
                return mostRecentPosition.name !== '' && mostRecentPosition.quantity > 0;
            });

            
            const fetchOrganizationPositions = async () => {
                // Get the positions from the organization and add them to the position list
                axios.get(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/organization/officer/${userStore.student_organization_id}`)
                .then((response) => {
                    const officers = response.data.officers;
                    
                    // Clear saved positions selection list if election type is changed
                    position_saved_selection.value = [];

                    // Clear each position name and value if election type is changed
                    position_list.value.forEach((position) => {
                        position.name = '';
                        position.value = '';
                    });

                    // Add the positions to the saved positions selection list and make sure its unique
                    officers.forEach((officer) => {
                        const savedPositionsSelection = {
                            name: officer.Position,
                            value: officer.Position,
                            is_already_selected: true,
                        };

                        // Check if the position already exists in the saved positions selection list
                        const positionExists = position_saved_selection.value.some((pos) => pos.name === savedPositionsSelection.name);

                        // If it doesn't exist, add it to the saved positions selection list
                        if (!positionExists) {
                            position_saved_selection.value.push(savedPositionsSelection);
                        }
                    });

                    // Add the positions to the position list use the first position as the default value
                    position_list.value = [];
                    officers.forEach((officer, index) => {
                        const position = {
                            count: index + 1,
                            name: officer.Position,
                            value: officer.Position,
                            quantity: 1,
                            is_re_usable: false,
                        };
                        position_list.value.push(position);
                    });
                })
                .catch((error) => {
                    console.log(error);
                });
            }

            const fetchAllOrganization = async () => {
                const response = await axios.get(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/student/organization/all`);
                console.log(`All organization fetched. Duration: ${response.duration}`)

                // Set the election_course_requirements
                const organization = response.data.student_organizations.find((organization) => organization.StudentOrganizationId === Number(userStore.student_organization_id));
                election_course_requirements.value = organization.OrganizationMemberRequirements;

                fetchOrganizationPositions();

                return response.data.student_organizations;
            }

            const { data: organizationData,
                    isLoading: isOrganizationLoading,
                    isSuccess: isOrganizationSuccess,
                    isError: isOrganizationError,
                    error: organizationError,} = 
                    useQuery({
                        queryKey: [`fetchAllOrganization`],
                        queryFn: fetchAllOrganization,
                    })

            return { 
                    createdByStudentNumber, 
                    submitting,
                    userStore,

                    election_name_input,
                    election_type_input,
                    election_course_requirements,
                    election_school_year_input,
                    election_semester_input,
                    election_start_input,
                    election_end_input,
                    election_filing_coc_start_input,
                    election_filing_coc_end_input,
                    election_campaign_start_input,
                    election_campaign_end_input,
                    election_voting_start_input,
                    election_voting_end_input,
                    election_appeal_start_input,
                    election_appeal_end_input,
                    position_count, 
                    position_list, 
                    isMostRecentPositionFilled, 
                    position_saved_selection, 

                    organizationData,
                    isOrganizationLoading,
                    isOrganizationError,
                    organizationError,
                }
        },
        components: { Navbar, Sidebar, ActionButton, SearchBarAndFilter, BaseContainer, BaseTable, ToolTip },
        props: {
            full_name: String,
            user_role: String,
        },
        computed:{
            nextFiveYears() {
                return this.getNextFiveYears();
            },
            createElectionButtonText() {
                return this.submitting ? 'Creating..' : 'Create Election';
            }
        },
        methods:{
            returnPage() {
                if (this.submitting) return;

                //const confirm = window.confirm('Are you sure you want to cancel and return? inputs will not be saved.');
                //if (!confirm) return;
                router.visit('/organization/elections');
            },
            getNextFiveYears() {
                const currentYear = new Date().getFullYear();

                // Return an array of the next five years
                return Array.from({length: 5}, (_, i) => currentYear + i);
            },
            addNewPosition() {
                this.position_count = this.position_count + 1;
                const newPosition = {
                    count: this.position_count,
                    name: '',
                    value: '',
                    quantity: 1,
                };
                this.position_list.push(newPosition);
                this.watchNewAddedPositionValue();
            },
            removePosition(index) {
                this.position_list.splice(index, 1);
                this.onPositionListSelect();
            },
            watchNewAddedPositionValue() {
                // Watch for the old as well as the new position list value

                this.position_list.forEach((position, index) => {
                    watch(() => position.value, (newVal) => {
                        if (newVal === 'New') {
                            position.name = '';
                        } 
                        else {
                            position.name = newVal;
                        }
                    });
                });

                this.position_list.forEach((position, index) => {
                    watch(() => position.name, (newName) => {
                        axios.get(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/election/position/reusable/all`)
                        .then((response) => {
                            const positions = response.data.positions;
                            const positionExists = positions.some((pos) => pos.PositionName === newName);

                            // Determine if can make reusable or remove reusability
                            if (positionExists) {
                                position.is_re_usable = false;
                            } 
                            else {
                                position.is_re_usable = true;
                            }
                        })
                        .catch((error) => {
                            console.log(error);
                        });
                    });
                });
            },
            refreshPositionListAndReusabilityState() {
                this.position_list.forEach((position, index) => {
                    axios.get(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/election/position/reusable/all`)
                    .then((response) => {
                        const positions = response.data.positions;
                        const positionExists = positions.some((pos) => pos.PositionName === position.name);

                        // Determine if can make reusable or remove reusability
                        if (positionExists) {
                            position.is_re_usable = false;
                        } 
                        else {
                            position.value = '';
                            position.name = '';
                            position.is_re_usable = true;
                        }

                        // Re-fetch the position saved selection list from the DB
                        this.position_saved_selection = [];
                        positions.forEach((pos) => {
                            const savedPositionsSelection = {
                                name: pos.PositionName,
                                value: pos.PositionName,
                                is_already_selected: false,
                            };
                            this.position_saved_selection.push(savedPositionsSelection);
                        });

                        // Re attach the correct is_already_selected value
                        this.onPositionListSelect();
                    })
                    .catch((error) => {
                        console.log(error);
                    });
                });
            },
            makePositionReusableOrNot(index){    
                const position = this.position_list[index]
                const name = position.name.trim();

                if (position.is_re_usable) {
                    axios.post(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/election/position/reusable/save`, {
                        name: name
                    }) 
                    .then((response) => {
                        this.refreshPositionListAndReusabilityState();
                        position.value = name.charAt(0).toUpperCase() + name.slice(1);

                        alert(response.data.message)
                        console.log(response.duration);
                    })
                    .catch((error) => {
                        console.log(error);
                    });
                }
                else {
                    axios.delete(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/election/position/reusable/delete`, {
                        data: {
                            name: name
                        }
                    })
                    .then((response) => {
                        this.refreshPositionListAndReusabilityState();

                        alert(response.data.message)
                        console.log(response.duration);
                    })
                    .catch((error) => {
                        console.log(error);
                    });
                }
            },
            onPositionListSelect(e) {
                // If found on the created position list, it means its already selected and should be disabled
                this.position_saved_selection.forEach(savedPosition => {
                    savedPosition.is_already_selected = this.position_list.some(position => position.name === savedPosition.name);
                });
            },
            validateInputs() {
                // Validates the election info inputs
                if (this.election_name_input === '') {
                    return alert('Election name cannot be empty');
                }
                if (this.election_type_input === '') {
                    return alert('Election student organization cannot be empty');
                }
                if (this.election_school_year_input === '') {
                    return alert('School year cannot be empty');
                }
                if (this.election_semester_input === '') {
                    return alert('Semester cannot be empty');
                }
                if (this.election_start_input === '') {
                    return alert('Election start cannot be empty');
                }
                if (this.election_end_input === '') {
                    return alert('Election end cannot be empty');
                }
                if (this.election_filing_coc_start_input === '') {
                    return alert('Filing start cannot be empty');
                }
                if (this.election_filing_coc_end_input === '') {
                    return alert('Filing end cannot be empty');
                }
                if (this.election_campaign_start_input === '') {
                    return alert('Campaign start cannot be empty');
                }
                if (this.election_campaign_end_input === '') {
                    return alert('Campaign end cannot be empty');
                }
                if (this.election_voting_start_input === '') {
                    return alert('Voting start cannot be empty');
                }
                if (this.election_voting_end_input === '') {
                    return alert('Voting end cannot be empty');
                }

                // Check if datetimes are progressive
                if (new Date(this.election_start_input) >= new Date(this.election_end_input)) {
                    return alert('Election start cannot be greater than or equal to election end');
                }
                if (new Date(this.election_filing_coc_start_input) >= new Date(this.election_filing_coc_end_input)) {
                    return alert('Filing start cannot be greater than or equal to filing end');
                }
                if (new Date(this.election_filing_coc_end_input) >= new Date(this.election_campaign_start_input)) {
                    return alert('Campaign start should be after filing end');
                }
                if (new Date(this.election_campaign_start_input) >= new Date(this.election_campaign_end_input)) {
                    return alert('Campaign start cannot be greater than or equal to campaign end');
                }
                if (new Date(this.election_campaign_end_input) >= new Date(this.election_voting_start_input)) {
                    return alert('Voting start should be after campaign end');
                }
                if (new Date(this.election_voting_start_input) >= new Date(this.election_voting_end_input)) {
                    return alert('Voting start cannot be greater than or equal to voting end');
                }

                // Create a set to store unique values
                const valueSet = new Set();

                // Validates the position list inputs
                for (const position of this.position_list) {
                    if (valueSet.has(position.name)) {
                        return alert('Duplicate position name found');
                    }
                    valueSet.add(position.name);
                }

                for (const position of this.position_list) {
                    if (position.name === '') {
                        return alert('Position name cannot be empty');
                    }
                }
                
                for (const position of this.position_list) {
                    if (position.quantity < 1) {
                        return alert('Position quantity cannot be less than 1 or empty');
                    }
                }

                return true;
            },
            submit() {
                // Validate inputs returns false if there are errors
                if (!this.validateInputs()) {
                    return;
                }

                const confirmCreate = window.confirm('Are you sure you want to create this election?');
                if (!confirmCreate) {
                    return;
                }

                const positionData = this.position_list.map(position => ({
                    value: position.name.trim(),
                    quantity: String(position.quantity)
                }));

                const electionData = {
                    election_name: this.election_name_input,
                    election_type: parseInt(this.election_type_input),
                    school_year: String(this.election_school_year_input),
                    semester: String(this.election_semester_input),
                    election_start: this.election_start_input,
                    election_end: this.election_end_input,
                    filing_coc_start: this.election_filing_coc_start_input,
                    filing_coc_end: this.election_filing_coc_end_input,
                    campaign_start: this.election_campaign_start_input,
                    campaign_end: this.election_campaign_end_input,
                    voting_start: this.election_voting_start_input,
                    voting_end: this.election_voting_end_input,
                    appeal_start: this.election_voting_end_input,
                    appeal_end: this.election_voting_end_input,
                    created_by: this.createdByStudentNumber
                };

                const data = {
                    positions: positionData,
                    election_info: electionData
                };

                this.submitting = true;

                axios.post(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/election/create`, data)
                    .then((response) => {
                        console.log(`Election created successfully. Duration: ${response.duration}`);
                        alert(response.data.message);
                        router.visit('/organization/elections');
                    })
                    .catch((error) => {
                        console.log(error);
                    })
                    .finally(() => {
                        this.submitting = false;
                    });
            }
        }
    }
</script>

<style scoped>
    .utilities{
        margin-bottom: 1%;
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
        background-color: #FDD5D5;
        box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.14), 0 6px 20px 0 rgba(0, 0, 0, 0.08);
        padding: 2%;
    }

    .note h6{
        margin-top: 10px;
    }

    .note-timeline{
        margin-top: 1.5%;
        background-color: #FDD5D5;
        padding: 1.1%;
    }

    .note-timeline h6{
        margin-top: 10px;
    }

    .box{
        background-color: white;
        box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.14), 0 6px 20px 0 rgba(0, 0, 0, 0.08);
        padding: 3%;
    }

    .upper-box{
        padding-bottom: 5%;
    }

    .position-box{
        background-color: white;
        box-shadow: 0 2px 8px 0 rgba(0, 0, 0, 0.14), 0 6px 20px 0 rgba(0, 0, 0, 0.08);
        padding: 3% 3% 2% 3%;
    }

    .margin{
        margin-bottom: 2%;
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
</style>