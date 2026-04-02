const API_URL = 'http://localhost:8080/backend/api';

export const api = {
  // 登入
  async login(email, password) {
    const response = await fetch(`${API_URL}/login.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
      },
      body: new URLSearchParams({ email, password })
    });
    return response.json();
  },

  // 打卡
  async checkin(latitude, longitude, type) {
    const token = localStorage.getItem('token');
    const response = await fetch(`${API_URL}/checkin.php`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/x-www-form-urlencoded',
        'Authorization': `Bearer ${token}`
      },
      body: new URLSearchParams({ latitude, longitude, type })
    });
    return response.json();
  },

  // 上傳照片
  async uploadPhoto(photoFile) {
    const token = localStorage.getItem('token');
    const formData = new FormData();
    formData.append('photo', photoFile);
    
    const response = await fetch(`${API_URL}/upload.php`, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`
      },
      body: formData
    });
    return response.json();
  },

  // 獲取 Dashboard 數據
  async getDashboard() {
    const token = localStorage.getItem('token');
    const response = await fetch(`${API_URL}/dashboard.php`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    return response.json();
  },

  // 獲取報表
  async getReports(startDate, endDate) {
    const token = localStorage.getItem('token');
    const response = await fetch(`${API_URL}/reports.php`, {
      headers: {
        'Authorization': `Bearer ${token}`
      }
    });
    return response.json();
  }
};
