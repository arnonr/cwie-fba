<script setup>
import StudentList from "@/components/student-list/TeacherVisitList.vue";
</script>

<template>
  <div>
    <StudentList :user_type="'chairman'" />
  </div>
</template>

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
