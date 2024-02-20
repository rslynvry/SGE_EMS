import { defineStore } from 'pinia';
import { useLocalStorage } from '@vueuse/core';

export const useUserStore = defineStore('user', () => {
    const id = useLocalStorage('id', '');
    const student_number = useLocalStorage('student_number', '');

    const full_name = useLocalStorage('full_name', '');
    const user_role = useLocalStorage('user_role', '');

    const organization_name = useLocalStorage('organization_name', '');
    const organization_position_id = useLocalStorage('organization_position_id', '');

    const student_organization_id = useLocalStorage('student_organization_id', '');
    const student_organization_name = useLocalStorage('student_organization_name', '');

    const reset = () => {
        id.value = '';
        student_number.value = '';
        full_name.value = '';
        user_role.value = '';
        organization_name.value = '';
        organization_position_id.value = '';
        student_organization_id.value = '';
        student_organization_name.value = '';
    };
    
    return { id, 
            student_number, 
            full_name, 
            user_role, 
            organization_name, 
            organization_position_id, 
            student_organization_id, 
            student_organization_name, 
            reset 
        };
});
