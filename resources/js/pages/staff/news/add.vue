<script setup>
import { requiredValidator } from "@validators";
import { useRoute, useRouter } from "vue-router";
import { useNewsStore } from "./useNewsStore";

import "froala-editor/css/froala_editor.pkgd.min.css";
import FroalaEditor from "froala-editor/js/froala_editor.pkgd.min.js";

const route = useRoute();
const router = useRouter();
const newsStore = useNewsStore();
const props = defineProps(["isDialogAddNewsVisible"]);
const emit = defineEmits([
  "toggle:isDialogAddCompanyVisible",
  "update:companyItem",
]);

let baseUrl = "http://co-op.fba.kmutnb.ac.th/api";
console.log(location.hostname);
if (location.hostname === "localhost" || location.hostname === "127.0.0.1") {
  baseUrl = "http://localhost:8117/api";
}

// console.log(issetprops.isDialogAddCompanyVisible);

const item = ref({
  id: null,
  news_title: "",
  news_detail: "",
  pinned: 0,
  news_file: [],
  active: 1,
});

let userData = JSON.parse(localStorage.getItem("userData"));

const isOverlay = ref(false);
const isFormValid = ref(false);
const refForm = ref();

const selectOptions = ref({
  actives: [
    { title: "Active", value: 1 },
    { title: "In Active", value: 0 },
  ],
  pins: [
    { title: "Yes", value: 1 },
    { title: "No", value: 0 },
  ],
});

const onSubmit = () => {
  isOverlay.value = true;
  refForm.value?.validate().then(({ valid }) => {
    if (valid) {
      newsStore
        .addNews({
          ...item.value,
          news_file:
            item.value.news_file.length !== 0 ? item.value.news_file[0] : null,
          news_file:
            item.value.news_file.length !== 0 ? item.value.news_file[0] : null,
          news_cate_id: 1,
          news_detail: item.value.news_detail.replace(
            '<p data-f-id="pbf" style="text-align: center; font-size: 14px; margin-top: 30px; opacity: 0.65; font-family: sans-serif;">Powered by <a href="https://www.froala.com/wysiwyg-editor?pb=1" title="Froala Editor">Froala Editor</a></p>',
            ""
          ),
        })
        .then((response) => {
          if (response.data.message == "success") {
            localStorage.setItem("added", 1);
            nextTick(() => {
              router.push({
                path: "/staff/news/view/" + response.data.data.id,
              });
            });
          } else {
            isOverlay.value = false;
            console.log("error");
          }
        })
        .catch((error) => {
          console.error(error);
          //   isOverlay.value = false;
        });
    }
    isOverlay.value = false;
  });
};

const initFroala = () => {
  new FroalaEditor("#detail", {
    height: 300,
    // inlineMode: false,
    pastePlain: true,
    paragraphy: false,
    quickInsertEnabled: false,
    toolbarButtons: [
      "undo",
      "redo",
      "fullscreen",
      "|",
      "fontSize",
      "color",
      "bold",
      "italic",
      "underline",
      "inlineClass",
      "|",
      "paragraphFormat",
      "align",
      "outdent",
      "indent",
      "|",
      "formatOL",
      "formatUL",
      "quote",
      "-",
      "insertLink",
      "insertImage",
      "insertVideo",
      "insertFile",
      "insertTable",
      "|",
      "fontAwesome",
      "insertHR",
      "selectAll",
      "clearFormatting",
      "|",
      "print",
      "getPDF",
      "html",
    ],
    // Change buttons for XS screen.
    toolbarButtonsXS: [
      ["undo", "redo"],
      ["bold", "italic", "underline"],
    ],
    placeholderText: "",
    attribution: false,
    key: "enter-your-license-key-here",
    disableRightClick: true,

    imageUploadURL: baseUrl + "/froala/image",
    imageAllowedTypes: ["jpeg", "jpg", "png"],

    fileUploadURL: baseUrl + "/froala/document",
    videoUploadURL: baseUrl + "/froala/video",

    // fileUpload: false,
    // imageUpload: false,
    imagePaste: false,
    imagePasteProcess: false,
    imageResize: true,
    crossDomain: true,
    events: {
      keyup: function (inputEvent) {
        item.value.detail = this.html.get();
      },
      click: function (clickEvent) {
        item.value.detail = this.html.get();
      },
      "commands.after": function (cmd, param1, param2) {
        item.value.detail = this.html.get();
      },
      "paste.after": function (pasteEvent) {
        item.value.detail = this.html.get();
      },
    },
  });

  new FroalaEditor("#news_detail", {
    height: 300,
    // inlineMode: false,
    pastePlain: true,
    paragraphy: false,
    quickInsertEnabled: false,
    toolbarButtons: [
      "undo",
      "redo",
      "fullscreen",
      "|",
      "fontSize",
      "color",
      "bold",
      "italic",
      "underline",
      "inlineClass",
      "|",
      "paragraphFormat",
      "align",
      "outdent",
      "indent",
      "|",
      "formatOL",
      "formatUL",
      "quote",
      "-",
      "insertLink",
      "insertImage",
      "insertVideo",
      "insertFile",
      "insertTable",
      "|",
      "fontAwesome",
      "insertHR",
      "selectAll",
      "clearFormatting",
      "|",
      "print",
      "getPDF",
      "html",
    ],
    // Change buttons for XS screen.
    toolbarButtonsXS: [
      ["undo", "redo"],
      ["bold", "italic", "underline"],
    ],
    placeholderText: "",
    attribution: false,
    key: "enter-your-license-key-here",
    disableRightClick: true,

    imageUploadURL: baseUrl + "/froala/image",
    // imageAllowedTypes: ['jpeg', 'jpg', 'png'],

    fileUploadURL: baseUrl + "/froala/document",
    videoUploadURL: baseUrl + "/froala/video",

    // fileUpload: false,
    // imageUpload: false,
    imagePaste: false,
    imagePasteProcess: false,
    imageResize: true,
    crossDomain: true,
    events: {
      keyup: function (inputEvent) {
        item.value.news_detail = this.html.get();
      },
      click: function (clickEvent) {
        item.value.news_detail = this.html.get();
      },
      "commands.after": function (cmd, param1, param2) {
        item.value.news_detail = this.html.get();
      },
      "paste.after": function (pasteEvent) {
        item.value.news_detail = this.html.get();
      },
    },
  });
};

onMounted(() => {
  initFroala();
  window.scrollTo(0, 0);
});
</script>

<template>
  <div>
    <VCard title="เพิ่มข่าว">
      <VCardItem>
        <VForm ref="refForm" v-model="isFormValid" @submit.prevent="onSubmit">
          <VRow class="mt-1 mb-1">
            <VCol cols="12" md="2">
              <span class="font-weight-bold">รูปปกข่าว : </span>
            </VCol>

            <VCol cols="12" md="10">
              <VFileInput
                label="Upload Cover"
                id="news_file"
                v-model="item.news_file"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="2">
              <label class="font-weight-bold">หัวข้อข่าว : </label>
            </VCol>
            <VCol cols="12" md="10">
              <AppTextField
                id="news_title"
                :rules="[requiredValidator]"
                v-model="item.news_title"
                placeholder="Title"
                persistent-placeholder
              />
            </VCol>

            <VCol cols="12" md="2">
              <label class="font-weight-bold">เนื้อหาข่าว : </label>
            </VCol>
            <VCol cols="12" md="10">
              <!-- <AppTextarea
                id="news_detail"
                v-model="item.news_detail"
                placeholder="Detail"
                persistent-placeholder
              /> -->

              <div id="news_detail"></div>
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="website"
                >สถานะ :
              </label>
              <AppSelect
                :items="selectOptions.actives"
                v-model="item.active"
                variant="outlined"
                placeholder="Status"
              />
            </VCol>

            <VCol cols="12" md="6" class="align-items-center">
              <label class="v-label font-weight-bold" for="pinned"
                >ปักหมุด :
              </label>
              <AppSelect
                :items="selectOptions.pins"
                v-model="item.pinned"
                variant="outlined"
                placeholder="Pin"
              />
            </VCol>

            <!-- 👉 submit and reset button -->
            <VCol cols="12" md="9" class="d-flex gap-4">
              <VBtn type="submit" color="success"> Submit </VBtn>
            </VCol>
            <!--  -->
          </VRow>
        </VForm>
      </VCardItem>
    </VCard>

    <VOverlay
      v-model="isOverlay"
      contained
      persistent
      class="align-center justify-center"
    >
      <VProgressCircular indeterminate />
    </VOverlay>
  </div>
</template>

<route lang="yaml">
meta:
  action: read
  subject: Auth
</route>
