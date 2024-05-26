<template>
  <title>Analytics Voting Receipts - EMS</title>
  <Sidebar></Sidebar>
  <Navbar></Navbar>

  <div class="components">
      <div class="row">
          <div class="header">
              <div class="col-12">
                  <h2 class="my-1 page-title">
                      <span class="return" @click="returnPage">Analytics</span> > View Receipts ({{ election_name }})
                  </h2>
              </div>
          </div>
      </div>
   
      <BaseContainer class="item-container" :height="'auto'" :maxHeight="'75vh'">
          <BaseTable class="item-table" 
                  :columns="['Receipt ID', 'Student Number', 'Date Created', 'Preview / Download']" 
                  :tableHeight="'auto'"
                  :maxTableHeight="'69vh'">
              <tr v-for="(receipt, index) in receiptsData" :key="index">
                  <td class="my-cell">{{ receipt.voting_receipt_id }}</td>
                  <td class="my-cell">{{ receipt.student_number }}</td>
                  <td class="my-cell">{{ toDate(receipt.created_at) }}</td>
                  <td class="my-cell">
                      <ActionButton 
                          @click="previewReceipt(receipt)" class="preview-button mx-2">
                          <i class="fas fa-eye"></i>
                      </ActionButton>
                      <ActionButton 
                          @click="downloadReceipt(receipt)" class="download-button">
                          <i class="fas fa-download"></i>
                      </ActionButton>
                  </td>
              </tr>
          </BaseTable>
      </BaseContainer>

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

  import { useQuery } from "@tanstack/vue-query";
  import axios from 'axios';

  export default {
      setup(props) {
          const fetchReceipts = async () => {
              const response = await axios.get(`${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/votings/election/${Number(props.id)}/receipts`);
              console.log(`Receipts successfully fetched. Duration: ${response.duration}`)

              return response.data.voting_receipts;
          };

          const { data: receiptsData, isLoading, isSuccess, isError } = 
              useQuery({
                  queryKey: [`fetchReceipts-${Number(props.id)}`],
                  queryFn: fetchReceipts,
              });

          return {
              receiptsData,
              isLoading,
              isSuccess,
              isError,
          }
      },
      components: {
          Navbar,
          Sidebar,
          ActionButton,
          BaseContainer,
          BaseTable,
          ImageSkeleton,
      },
      props: {
          id: {
              type: String,
              required: true,
          },
          election_name: {
              type: String,
              required: true,
          }
      },
      methods: {
          returnPage(){
              router.visit('/comelec/analytics');
          },
          toDate(date){
              return new Date(date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
          },
          previewReceipt(receipt){
              window.open(receipt.receipt_pdf, '_blank')
          },
          downloadReceipt(receipt){
              axios({
                  url: `${import.meta.env.VITE_FASTAPI_BASE_URL}/api/v1/votings/receipt/${receipt.voting_receipt_id}/download`,
                  method: 'GET',
                  responseType: 'blob', // important
              })
              .then((response) => {
                  const url = window.URL.createObjectURL(new Blob([response.data]));
                  const link = document.createElement('a');
                  
                  // Use the student number as the filename
                  link.href = url;
                  link.setAttribute('download', `${receipt.student_number}-Voting-Receipt.pdf`); // replace with actual student number attribute

                  document.body.appendChild(link);
                  link.click();
              })
          }
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
</style>