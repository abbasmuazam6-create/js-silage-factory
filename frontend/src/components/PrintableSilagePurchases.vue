<template>
  <div class="print-container">
    <div class="print-header">
      <img src="https://i.ibb.co/xKG273Sm/JS-Final-logo.jpg" class="print-logo" />
      <h1>JS Silage & Wanda Factory</h1>
      <p class="print-sub">Silage Purchases</p>
      <p class="print-date">Generated: {{ new Date().toLocaleString() }}</p>
    </div>
    <table class="print-table">
      <thead>
        <tr><th>#</th><th>Code</th><th>Supplier</th><th>Date</th><th>Weight (kg)</th><th>Cost ($)</th></tr>
      </thead>
      <tbody>
        <tr v-for="(p, idx) in data" :key="p.id">
          <td>{{ idx+1 }}</td>
          <td>{{ p.purchase_code }}</td>
          <td>{{ p.supplier?.name || '-' }}</td>
          <td>{{ formatDate(p.purchase_date) }}</td>
          <td>{{ numberFormat(p.weight_kg) }}</td>
          <td>{{ numberFormat(p.cost) }}</td>
        </tr>
      </tbody>
      <tfoot>
        <tr><td colspan="4" class="text-right"><strong>Totals</strong></td><td>{{ numberFormat(totalWeight) }}</td><td>{{ numberFormat(totalCost) }}</td></tr>
      </tfoot>
    </table>
    <div class="print-footer">© {{ new Date().getFullYear() }} JS Silage & Wanda Factory</div>
  </div>
</template>

<script setup>
import { defineProps } from 'vue'
const props = defineProps({ data: Array, totalWeight: Number, totalCost: Number })
const numberFormat = (v) => (v == null ? '0' : Number(v).toLocaleString())
const formatDate = (d) => d ? new Date(d).toLocaleDateString() : '-'
</script>

<style scoped>
.print-container { font-family: Arial; max-width:1000px; margin:0 auto; padding:20px; background:white; }
.print-header { display:flex; align-items:center; gap:15px; border-bottom:2px solid #0ea5e9; padding-bottom:10px; margin-bottom:20px; }
.print-logo { max-height:40px; max-width:80px; height:40px; width:80px; object-fit:contain; flex-shrink:0; }
.print-header h1 { font-size:22px; color:#0c4a6e; margin:0; }
.print-sub { font-size:16px; color:#1e293b; margin:0; }
.print-date { font-size:12px; color:#64748b; margin-left:auto; }
.print-table { width:100%; border-collapse:collapse; font-size:14px; }
.print-table th { background:#f0f9ff; color:#0c4a6e; font-weight:600; padding:8px 12px; border:1px solid #bae6fd; text-align:left; }
.print-table td { padding:6px 12px; border:1px solid #e0f2fe; }
.print-table tfoot { font-weight:600; background:#f8fafc; }
.print-footer { margin-top:20px; border-top:1px solid #ccc; padding-top:10px; text-align:center; font-size:12px; color:#94a3b8; }
@media print { body * { visibility:hidden; } .print-container, .print-container * { visibility:visible; } .print-container { position:absolute; left:0; top:0; width:100%; } }
</style>