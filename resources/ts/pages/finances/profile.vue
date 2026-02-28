<script setup lang="ts">
/**
 * My Finances — Profile. FREEDOMSTACK_USER_JOURNEY.md §6.3
 * URL: /finances/profile. Personal and income information.
 */
definePage({
  meta: {
    layout: 'dashboard-shell',
  },
})

const loading = ref(false)
const saved = ref(false)
const form = ref({
  monthly_gross_income: '',
  monthly_net_income: '',
  monthly_expenses_total: '',
  filing_status: 'single',
  state_of_residence: '',
  target_fire_age: '',
  current_age: '',
  risk_tolerance: 'moderate',
})

onMounted(async () => {
  try {
    const res = await $api<{ data: any }>('/profile')
    if (res.data) {
      form.value = {
        monthly_gross_income: String((res.data.monthly_gross_income ?? 0) / 100),
        monthly_net_income: String((res.data.monthly_net_income ?? 0) / 100),
        monthly_expenses_total: String((res.data.monthly_expenses_total ?? 0) / 100),
        filing_status: res.data.filing_status ?? 'single',
        state_of_residence: res.data.state_of_residence ?? '',
        target_fire_age: res.data.target_fire_age ?? '',
        current_age: res.data.current_age ?? '',
        risk_tolerance: res.data.risk_tolerance ?? 'moderate',
      }
    }
  } catch (_) {}
})

async function submit() {
  loading.value = true
  saved.value = false
  try {
    await $api('/profile', {
      method: form.value.monthly_gross_income ? 'PUT' : 'POST',
      body: {
        monthly_gross_income: Math.round(parseFloat(form.value.monthly_gross_income || '0') * 100),
        monthly_net_income: Math.round(parseFloat(form.value.monthly_net_income || '0') * 100),
        monthly_expenses_total: Math.round(parseFloat(form.value.monthly_expenses_total || '0') * 100),
        filing_status: form.value.filing_status,
        state_of_residence: form.value.state_of_residence || null,
        target_fire_age: form.value.target_fire_age ? parseInt(form.value.target_fire_age, 10) : null,
        current_age: form.value.current_age ? parseInt(form.value.current_age, 10) : null,
        risk_tolerance: form.value.risk_tolerance,
      },
    })
    saved.value = true
  } catch (_) {}
  finally {
    loading.value = false
  }
}
</script>

<template>
  <VRow>
    <VCol cols="12">
      <h1 class="text-h3 mb-4">
        Profile
      </h1>
      <p class="text-body-1 text-medium-emphasis mb-6">
        Manage personal and income information. My Finances → Profile.
      </p>
    </VCol>
    <VCol cols="12" md="8">
      <VCard>
        <VCardText>
          <VForm @submit.prevent="submit">
            <VRow>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="form.monthly_gross_income"
                  type="number"
                  min="0"
                  step="0.01"
                  label="Monthly gross income ($)"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="form.monthly_net_income"
                  type="number"
                  min="0"
                  step="0.01"
                  label="Monthly net income ($)"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="form.monthly_expenses_total"
                  type="number"
                  min="0"
                  step="0.01"
                  label="Monthly expenses total ($)"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="form.filing_status"
                  label="Filing status"
                  :items="[
                    { value: 'single', title: 'Single' },
                    { value: 'married_joint', title: 'Married (joint)' },
                    { value: 'married_separate', title: 'Married (separate)' },
                    { value: 'head_of_household', title: 'Head of household' },
                  ]"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="form.state_of_residence"
                  label="State (2-letter)"
                  maxlength="2"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppSelect
                  v-model="form.risk_tolerance"
                  label="Risk tolerance"
                  :items="[
                    { value: 'conservative', title: 'Conservative' },
                    { value: 'moderate', title: 'Moderate' },
                    { value: 'aggressive', title: 'Aggressive' },
                  ]"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="form.current_age"
                  type="number"
                  min="18"
                  max="100"
                  label="Current age"
                />
              </VCol>
              <VCol cols="12" md="6">
                <AppTextField
                  v-model="form.target_fire_age"
                  type="number"
                  min="18"
                  max="100"
                  label="Target freedom age"
                />
              </VCol>
            </VRow>
            <VAlert v-if="saved" type="success" class="mt-4" closable>
              Profile saved.
            </VAlert>
            <VBtn type="submit" color="primary" class="mt-4" :loading="loading">
              Save profile
            </VBtn>
          </VForm>
        </VCardText>
      </VCard>
    </VCol>
  </VRow>
</template>
