import { defineStore } from 'pinia'
import axios from 'axios'

export const useSaleStore = defineStore('sale', {
  actions: {
    async deleteSale(id) {
      await axios.delete(`/sales/${id}`)
    },
  },
})