<script setup>
import StudentList from "@/components/student-list/StudentMapTeacherList.vue";
</script>

<template>
  <div>
    <StudentList :user_type="'staff'" />
  </div>
</template>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
