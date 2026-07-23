import axios from 'axios'

export const fetchSeasons = async () => {
  try {
    const token = localStorage.getItem('token')
    const { data } = await axios.get('/seasons', {
      headers: { Authorization: `Bearer ${token}` }
    })
    return data
  } catch (err) {
    console.error('Failed to fetch seasons:', err)
    return []
  }
}

export const getCurrentSeason = (seasons) => {
  return seasons.find(s => s.is_current) || null
}