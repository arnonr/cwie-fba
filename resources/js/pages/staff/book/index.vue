<script setup>
import StudentList from "@/components/book/BookList.vue";
</script>

<template>
  <div>
    <StudentList :user_type="'staff'" :book_type="'book_request'" />
  </div>
</template>

<style lang="scss"></style>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
