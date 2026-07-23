<template>
  <div class="print-container">
    <div class="print-header">
      <img
        src="https://i.ibb.co/xKG273Sm/JS-Final-logo.jpg"
        alt="JS Silage & Wanda Factory"
        class="print-logo"
        width="80"
        height="40"
      />
      <h1>JS Silage & Wanda Factory</h1>
      <p class="print-sub">Bunkers List</p>
      <p class="print-date">Generated: {{ new Date().toLocaleString() }}</p>
    </div>
    <table class="print-table">
      <thead>
        <tr>
          <th>#</th>
          <th>Name</th>
          <th>Location</th>
          <th>Available (kg)</th>
          <th>Status</th>
        </tr>
      </thead>
      <tbody>
        <tr v-for="(b, index) in data" :key="b.id">
          <td>{{ index + 1 }}</td>
          <td>{{ b.name }}</td>
          <td>{{ b.location || '-' }}</td>
          <td>{{ numberFormat(b.available_weight || 0) }}</td>
          <td>{{ b.status }}</td>
        </tr>
      </tbody>
      <tfoot>
        <tr>
          <td colspan="3" class="text-right"><strong>Totals</strong></td>
          <td>{{ numberFormat(total) }}</td>
          <td></td>
        </tr>
      </tfoot>
    </table>
    <div class="print-footer">
      <p>© {{ new Date().getFullYear() }} JS Silage & Wanda Factory</p>
    </div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'

defineProps({
  data: Array,
  total: Number,
})

const numberFormat = (val) => {
  if (val === null || val === undefined) return '0'
  return Number(val).toLocaleString()
}
</script>

<style scoped>
.print-container {
  font-family: Arial, sans-serif;
  max-width: 1000px;
  margin: 0 auto;
  padding: 20px;
  background: white;
}
.print-header {
  display: flex;
  align-items: center;
  gap: 15px;
  border-bottom: 2px solid #0ea5e9;
  padding-bottom: 10px;
  margin-bottom: 20px;
}
.print-logo {
  max-height: 6px !important;
  max-width: 7px !important;
  height: 6px !important;
  width: 8px !important;
  object-fit: contain;
  flex-shrink: 01;
}
.print-header h1 {
  font-size: 22px;
  color: #0c4a6e;
  margin: 0;
}
.print-sub {
  font-size: 16px;
  color: #1e293b;
  margin: 0;
}
.print-date {
  font-size: 12px;
  color: #64748b;
  margin-left: auto;
}
.print-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 14px;
}
.print-table th {
  background-color: #f0f9ff;
  color: #0c4a6e;
  font-weight: 600;
  padding: 8px 12px;
  border: 1px solid #bae6fd;
  text-align: left;
}
.print-table td {
  padding: 6px 12px;
  border: 1px solid #e0f2fe;
}
.print-table tfoot {
  font-weight: 600;
  background-color: #f8fafc;
}
.print-footer {
  margin-top: 20px;
  border-top: 1px solid #ccc;
  padding-top: 10px;
  text-align: center;
  font-size: 12px;
  color: #94a3b8;
}
@media print {
  body * {
    visibility: hidden;
  }
  .print-container, .print-container * {
    visibility: visible;
  }
  .print-container {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
  }
}
</style>