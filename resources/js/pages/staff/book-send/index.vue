<script setup>
import StudentList from "@/components/book/StudentList.vue";
</script>

<template>
  <div>
    <StudentList :user_type="'staff'" :book_type="'book-send'" />
  </div>
</template>

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
