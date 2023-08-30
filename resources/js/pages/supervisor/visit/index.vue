<script setup>
import StudentList from "@/components/student-list/StudentVisitList.vue";
</script>

<template>
  <div>
    <StudentList :user_type="'supervisor'" />
  </div>
</template>

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
