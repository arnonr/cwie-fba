<script setup>
import StudentList from "@/components/student-list/StudentList.vue";
</script>

<template>
  <div>
    <StudentList :user_type="'staff'" />
  </div>
</template>

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
