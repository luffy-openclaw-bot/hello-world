import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

const API_URL = 'http://localhost:8080/backend/api';

function Dashboard() {
  const navigate = useNavigate();
  const [stats, setStats] = useState(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    fetchStats();
  }, []);

  const fetchStats = async () => {
    const token = localStorage.getItem('token');
    try {
      const response = await fetch(`${API_URL}/dashboard.php`, {
        headers: {
          'Authorization': `Bearer ${token}`
        }
      });
      const data = await response.json();
      if (data.status === 'success') {
        setStats(data.data);
      }
    } catch (err) {
      console.error('Failed to fetch stats');
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    navigate('/login');
  };

  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center">
        <div className="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      </div>
    );
  }

  return (
    <div className="min-h-screen bg-gray-50">
      <header className="bg-white shadow">
        <div className="max-w-7xl mx-auto py-6 px-4 flex justify-between items-center">
          <h1 className="text-2xl font-bold">📊 管理員 Dashboard</h1>
          <button onClick={handleLogout} className="text-gray-600 hover:text-gray-900">登出</button>
        </div>
      </header>

      <main className="max-w-7xl mx-auto py-6 px-4">
        {/* Stats Cards */}
        <div className="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-gray-600 text-sm">總員工數</h3>
            <p className="text-3xl font-bold text-blue-600">{stats?.total || 0}</p>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-gray-600 text-sm">已打卡</h3>
            <p className="text-3xl font-bold text-green-600">{stats?.checked_in || 0}</p>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-gray-600 text-sm">早走</h3>
            <p className="text-3xl font-bold text-warning-600">{stats?.early_leave || 0}</p>
          </div>
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-gray-600 text-sm">缺席</h3>
            <p className="text-3xl font-bold text-red-600">{stats?.absent || 0}</p>
          </div>
        </div>

        {/* Map Placeholder */}
        <div className="bg-white rounded-lg shadow p-6 mb-6">
          <h3 className="text-lg font-bold mb-4">📍 員工位置</h3>
          <div className="bg-gray-100 h-96 rounded flex items-center justify-center">
            <p className="text-gray-500">地圖組件（Leaflet.js）</p>
          </div>
        </div>

        {/* Recent Checkins */}
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-bold mb-4">今日打卡記錄</h3>
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left py-2">姓名</th>
                  <th className="text-left py-2">上班時間</th>
                  <th className="text-left py-2">下班時間</th>
                  <th className="text-left py-2">狀態</th>
                </tr>
              </thead>
              <tbody>
                <tr className="border-b">
                  <td className="py-2">陳大文</td>
                  <td className="py-2">09:00:00</td>
                  <td className="py-2">-</td>
                  <td className="py-2"><span className="bg-green-100 text-green-700 px-2 py-1 rounded">正常</span></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </main>
    </div>
  );
}

export default Dashboard;
