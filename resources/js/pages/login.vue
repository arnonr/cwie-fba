<script setup>
import { useAppAbility } from "@/plugins/casl/useAppAbility";
import axios from "@axios";
import { useGenerateImageVariant } from "@core/composable/useGenerateImageVariant";
// import authV2LoginIllustrationBorderedDark from "@images/pages/auth-v2-login-illustration-bordered-dark.png";
// import authV2LoginIllustrationBorderedLight from "@images/pages/auth-v2-login-illustration-bordered-light.png";
// import authV2LoginIllustrationDark from "@images/pages/auth-v2-login-illustration-dark.png";
// import authV2LoginIllustrationLight from "@images/pages/auth-v2-login-illustration-light.png";
import authV2MaskDark from "@images/pages/misc-mask-dark.png";
import authV2MaskLight from "@images/pages/misc-mask-light.png";
import { VNodeRenderer } from "@layouts/components/VNodeRenderer";
import { themeConfig } from "@themeConfig";
import { requiredValidator } from "@validators";
import { VForm } from "vuetify/components/VForm";

import authFBA from "@images/pages/login-fba-banner.png";

// const authThemeImg = useGenerateImageVariant(
//   authV2LoginIllustrationLight,
//   authV2LoginIllustrationDark,
//   authV2LoginIllustrationBorderedLight,
//   authV2LoginIllustrationBorderedDark,
//   true
// );
const authThemeMask = useGenerateImageVariant(authV2MaskLight, authV2MaskDark);
const isPasswordVisible = ref(false);
const route = useRoute();
const router = useRouter();
const ability = useAppAbility();

const errors = ref({
  username: undefined,
  password: undefined,
});

const refVForm = ref();
const username = ref("");
const password = ref("");
const rememberMe = ref(false);

const isSnackbarVisible = ref(false);
const snackbarText = ref("");
const snackbarColor = ref("success");

const login = () => {
  let dataSend = {
    username: username.value,
    password: password.value,
  };

  axios
    .post(`/auth/login/`, dataSend, {
      validateStatus: () => true,
    })
    .then((r) => {
      if (r.status === 200) {
        // const { accessToken, userData, userAbilities } = r.data;
        const { accessToken, userData, teacherData, chairman, majorHead } =
          r.data;

        // localStorage.setItem("userAbilities", JSON.stringify(userAbilities));
        // ability.update(userAbilities);
        console.log(r);
        console.log(r.data);
        // console.log(r.data.userData);

        userData.role = "";

        userData.ability = [
          {
            subject: "Auth",
            action: "manage",
          },
          {
            subject: "Auth",
            action: "read",
          },
        ];

        if (userData.account_type == 1) {
          userData.role = "student";
          userData.ability.push({
            subject: "StudentUser",
            action: "manage",
          });
        } else if (userData.account_type == 2) {
          userData.role = "teacher";
          userData.ability.push({
            subject: "TeacherUser",
            action: "manage",
          });

          if (chairman == true) {
            userData.ability.push({
              subject: "ChairmanUser",
              action: "manage",
            });
          }

          if (majorHead == true) {
            userData.ability.push({
              subject: "MajorHeadUser",
              action: "manage",
            });
          }
        } else if (userData.account_type == 3) {
          userData.role = "staff";
          userData.ability.push({
            subject: "StaffUser",
            action: "manage",
          });
        } else {
          userData.role = "admin";
          userData.ability.push({
            subject: "StaffUser",
            action: "manage",
          });
          userData.ability.push({
            subject: "AdminUser",
            action: "manage",
          });
        }

        localStorage.setItem("userAbilities", JSON.stringify(userData.ability));
        ability.update(userData.ability);
        localStorage.setItem("userData", JSON.stringify(userData));
        localStorage.setItem("teacherData", JSON.stringify(teacherData));
        localStorage.setItem("accessToken", JSON.stringify(accessToken));

        // Redirect to `to` query if exist or redirect to index route
        router.replace(route.query.to ? String(route.query.to) : "/");
        //
      } else {
        // console.log(r);
        snackbarText.value = r.data.message;
        snackbarColor.value = "danger";
        isSnackbarVisible.value = true;

        // console.log("ERROR1");
        // const { errors: formErrors } = {
        //   errors: { username: [r.data.error.message] },
        // };
        // errors.value = formErrors;
      }
    })
    .catch((e) => {
      snackbarText.value = e;
      snackbarColor.value = "danger";
      isSnackbarVisible.value = true;
    });
};

const onSubmit = () => {
  refVForm.value?.validate().then(({ valid: isValid }) => {
    if (isValid) login();
  });
};
</script>

<template>
  <VRow no-gutters class="auth-wrapper bg-surface">
    <VCol lg="8" class="d-none d-lg-flex">
      <div class="position-relative bg-background rounded-lg w-100 ma-8 me-0">
        <div class="d-flex align-center justify-center w-100 h-100">
          <VImg
            max-width="505"
            :src="authFBA"
            class="auth-illustration mt-16 mb-2"
          />
        </div>

        <VImg :src="authThemeMask" class="auth-footer-mask" />
      </div>
    </VCol>

    <VCol
      cols="12"
      lg="4"
      class="auth-card-v2 d-flex align-center justify-center"
    >
      <VCard flat :max-width="500" class="mt-12 mt-sm-0 pa-4">
        <VCardText align="center">
          <div>
            <VNodeRenderer :nodes="themeConfig.app.logo" class="mb-6" />
          </div>

          <h5 class="text-h5 mb-1">
            Welcome to
            <span class="text-capitalize"> {{ themeConfig.app.title }} </span>
          </h5>
          <p class="mb-0">Please sign-in with icit account</p>
        </VCardText>
        <VCardText>
          <VForm ref="refVForm" @submit.prevent="onSubmit">
            <VRow>
              <!-- email -->
              <VCol cols="12">
                <AppTextField
                  v-model="username"
                  label="ICIT Account"
                  type="text"
                  autofocus
                  :rules="[requiredValidator]"
                  :error-messages="errors.username"
                />
              </VCol>

              <!-- password -->
              <VCol cols="12">
                <AppTextField
                  v-model="password"
                  label="Password"
                  :rules="[requiredValidator]"
                  :type="isPasswordVisible ? 'text' : 'password'"
                  :error-messages="errors.password"
                  :append-inner-icon="
                    isPasswordVisible ? 'tabler-eye-off' : 'tabler-eye'
                  "
                  @click:append-inner="isPasswordVisible = !isPasswordVisible"
                />

                <div
                  class="d-flex align-center flex-wrap justify-space-between mt-2 mb-4"
                >
                  <VCheckbox v-model="rememberMe" label="Remember me" />
                  <a
                    class="text-primary ms-2 mb-1"
                    href="https://account.kmutnb.ac.th/web/recovery/index"
                    target="_blank"
                  >
                    Forgot Password?
                  </a>
                </div>

                <VBtn block type="submit"> Login </VBtn>
              </VCol>
            </VRow>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>

    <VCol>
      <VSnackbar
        v-model="isSnackbarVisible"
        location="top end"
        :color="snackbarColor"
      >
        {{ snackbarText }}
        <template #actions>
          <VBtn color="error" @click="isSnackbarVisible = false"> Close </VBtn>
        </template>
      </VSnackbar>
    </VCol>
  </VRow>
</template>

<style lang="scss">
@use "@core-scss/template/pages/page-auth.scss";
</style>

<route lang="yaml">
meta:
  layout: blank
  action: read
  subject: Auth
  redirectIfLoggedIn: true
</route>
