<template>
  <div v-if="visible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-2xl max-h-[90vh] overflow-y-auto rounded-lg bg-white p-6 shadow-xl">
      <div class="flex items-center justify-between border-b border-slate-200 pb-3">
        <h3 class="text-base font-bold text-slate-700">Customer Details</h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600">✕</button>
      </div>

      <div v-if="customer" class="mt-4 space-y-4">
        <div class="grid grid-cols-2 gap-3">
          <div>
            <label class="block text-xs font-medium text-slate-500">Name</label>
            <p class="text-sm font-medium text-slate-700">{{ customer.name }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Phone</label>
            <p class="text-sm font-medium text-slate-700">{{ customer.phone || '-' }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Email</label>
            <p class="text-sm font-medium text-slate-700">{{ customer.email || '-' }}</p>
          </div>
          <div>
            <label class="block text-xs font-medium text-slate-500">Contact Person</label>
            <p class="text-sm font-medium text-slate-700">{{ customer.contact_person || '-' }}</p>
          </div>
          <div class="col-span-2">
            <label class="block text-xs font-medium text-slate-500">Address</label>
            <p class="text-sm font-medium text-slate-700">{{ customer.address || '-' }}</p>
          </div>
          <div class="col-span-2">
            <label class="block text-xs font-medium text-slate-500">Delivery Address</label>
            <p class="text-sm font-medium text-slate-700">{{ customer.delivery_address || '-' }}</p>
          </div>
        </div>

        <div class="bg-primary-50 rounded-lg p-4 border border-primary-100">
          <div class="flex items-center justify-between">
            <h4 class="text-sm font-semibold text-slate-700">💰 Dues Summary</h4>
            <button
              v-if="(customer.total_due || 0) > 0"
              @click="openPayModal"
              class="btn-primary px-3 py-1 text-xs"
            >
              💳 Pay
            </button>
          </div>
          <div class="grid grid-cols-3 gap-4 text-center mt-2">
            <div>
              <p class="text-xs text-slate-500">Total Sales</p>
              <p class="text-sm font-bold text-slate-700">Rs. {{ numberFormat(customer.total_sales || 0) }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Paid Amount</p>
              <p class="text-sm font-bold text-green-600">Rs. {{ numberFormat(customer.total_paid || 0) }}</p>
            </div>
            <div>
              <p class="text-xs text-slate-500">Due Amount</p>
              <p class="text-sm font-bold" :class="(customer.total_due || 0) > 0 ? 'text-red-600' : 'text-green-600'">
                Rs. {{ numberFormat(customer.total_due || 0) }}
              </p>
            </div>
          </div>
        </div>

        <div v-if="customer.sales && customer.sales.length > 0">
          <h4 class="text-sm font-semibold text-slate-700 mb-2">📋 Sales History</h4>
          <div class="overflow-x-auto">
            <table class="w-full text-xs">
              <thead>
                <tr class="text-slate-500 border-b border-slate-200">
                  <th class="px-2 py-1 text-left">Invoice</th>
                  <th class="px-2 py-1 text-left">Date</th>
                  <th class="px-2 py-1 text-right">Total</th>
                  <th class="px-2 py-1 text-right">Paid</th>
                  <th class="px-2 py-1 text-right">Due</th>
                  <th class="px-2 py-1 text-left">Status</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="sale in customer.sales" :key="sale.id" class="border-b border-slate-100">
                  <td class="px-2 py-1">{{ sale.invoice_number }}</td>
                  <td class="px-2 py-1">{{ formatDate(sale.date) }}</td>
                  <td class="px-2 py-1 text-right">Rs. {{ numberFormat(sale.total_price) }}</td>
                  <td class="px-2 py-1 text-right">Rs. {{ numberFormat(sale.paid_amount) }}</td>
                  <td class="px-2 py-1 text-right" :class="sale.due_amount > 0 ? 'text-red-600' : ''">
                    Rs. {{ numberFormat(sale.due_amount) }}
                  </td>
                  <td class="px-2 py-1">
                    <span class="px-1.5 py-0.5 rounded-full text-[10px]" :class="sale.due_amount > 0 ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700'">
                      {{ sale.due_amount > 0 ? 'Due' : 'Paid' }}
                    </span>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="border-t border-slate-200 pt-3 flex justify-end">
          <button @click="$emit('close')" class="btn-outline px-4 py-2 text-sm">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Pay Modal -->
  <div v-if="payModalVisible" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-lg bg-white p-6 shadow-xl">
      <h3 class="text-base font-bold text-slate-700 mb-4">Record Payment</h3>
      <form @submit.prevent="savePayment" class="space-y-3">
        <div>
          <label class="block text-xs font-medium text-slate-700">Sale Invoice</label>
          <select
            v-model="payForm.sale_id"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
          >
            <option value="">Select Sale</option>
            <option
              v-for="sale in customer.sales?.filter(s => s.due_amount > 0)"
              :key="sale.id"
              :value="sale.id"
            >
              {{ sale.invoice_number }} - Due: Rs. {{ numberFormat(sale.due_amount) }}
            </option>
          </select>
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-700">Amount *</label>
          <input
            v-model.number="payForm.amount"
            type="number"
            step="0.01"
            min="0.01"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-700">Date *</label>
          <input
            v-model="payForm.date"
            type="date"
            required
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
          />
        </div>
        <div>
          <label class="block text-xs font-medium text-slate-700">Notes</label>
          <textarea
            v-model="payForm.notes"
            rows="2"
            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
            placeholder="Payment notes..."
          ></textarea>
        </div>
        <div v-if="payError" class="text-sm text-red-600">{{ payError }}</div>
        <div class="flex justify-end gap-3 border-t border-slate-200 pt-3">
          <button type="button" @click="payModalVisible = false" class="btn-outline px-4 py-2 text-sm">Cancel</button>
          <button type="submit" class="btn-primary px-4 py-2 text-sm" :disabled="payLoading">
            {{ payLoading ? 'Processing...' : 'Record Payment' }}
          </button>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import axios from 'axios'

const props = defineProps({
  visible: Boolean,
  customer: Object,
})

const emit = defineEmits(['close', 'saved'])

const payModalVisible = ref(false)
const payLoading = ref(false)
const payError = ref('')
const payForm = ref({
  sale_id: '',
  amount: 0,
  date: new Date().toISOString().split('T')[0],
  notes: '',
})

const numberFormat = (val) => {
  if (val === null || val === undefined) return '0'
  const num = Number(val)
  if (isNaN(num)) return '0'
  return num.toLocaleString()
}

const formatDate = (date) => {
  if (!date) return '-'
  return new Date(date).toLocaleDateString()
}

const getAuthHeader = () => {
  const token = localStorage.getItem('token')
  return { Authorization: `Bearer ${token}` }
}

const openPayModal = () => {
  const dueSales = props.customer.sales?.filter(s => s.due_amount > 0) || []
  if (dueSales.length === 0) {
    alert('No pending dues for this customer.')
    return
  }
  payForm.value.sale_id = dueSales[0].id
  payForm.value.amount = dueSales[0].due_amount
  payForm.value.date = new Date().toISOString().split('T')[0]
  payForm.value.notes = ''
  payError.value = ''
  payModalVisible.value = true
}

const savePayment = async () => {
  payError.value = ''
  if (!payForm.value.sale_id) {
    payError.value = 'Please select a sale'
    return
  }
  if (!payForm.value.amount || payForm.value.amount <= 0) {
    payError.value = 'Please enter a valid amount'
    return
  }

  payLoading.value = true
  try {
    await axios.post('/sales/payment', {
      sale_id: payForm.value.sale_id,
      amount: payForm.value.amount,
      date: payForm.value.date,
      notes: payForm.value.notes,
    }, {
      headers: getAuthHeader()
    })
    payModalVisible.value = false
    emit('saved')
    emit('close')
  } catch (err) {
    payError.value = err.response?.data?.message || 'Failed to record payment'
  } finally {
    payLoading.value = false
  }
}
</script>