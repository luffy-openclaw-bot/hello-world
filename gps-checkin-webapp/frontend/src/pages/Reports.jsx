import { useState } from 'react';
import { useNavigate } from 'react-router-dom';

function Reports() {
  const navigate = useNavigate();
  const [dateRange, setDateRange] = useState({
    start: '',
    end: ''
  });

  const handleExport = () => {
    // Mock export functionality
    alert('報表導出功能（Mock）');
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    navigate('/login');
  };

  return (
    <div className="min-h-screen bg-gray-50">
      <header className="bg-white shadow">
        <div className="max-w-7xl mx-auto py-6 px-4 flex justify-between items-center">
          <h1 className="text-2xl font-bold">📋 報表系統</h1>
          <button onClick={handleLogout} className="text-gray-600 hover:text-gray-900">登出</button>
        </div>
      </header>

      <main className="max-w-7xl mx-auto py-6 px-4">
        {/* Filters */}
        <div className="bg-white rounded-lg shadow p-6 mb-6">
          <h3 className="text-lg font-bold mb-4">篩選條件</h3>
          <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">開始日期</label>
              <input
                type="date"
                value={dateRange.start}
                onChange={(e) => setDateRange({ ...dateRange, start: e.target.value })}
                className="w-full px-3 py-2 border rounded"
              />
            </div>
            <div>
              <label className="block text-sm font-medium text-gray-700 mb-2">結束日期</label>
              <input
                type="date"
                value={dateRange.end}
                onChange={(e) => setDateRange({ ...dateRange, end: e.target.value })}
                className="w-full px-3 py-2 border rounded"
              />
            </div>
            <div className="flex items-end">
              <button
                onClick={handleExport}
                className="w-full bg-blue-600 text-white py-2 px-4 rounded hover:bg-blue-700"
              >
                📊 導出 CSV
              </button>
            </div>
          </div>
        </div>

        {/* Report Table */}
        <div className="bg-white rounded-lg shadow p-6">
          <h3 className="text-lg font-bold mb-4">打卡記錄</h3>
          <div className="overflow-x-auto">
            <table className="min-w-full">
              <thead>
                <tr className="border-b">
                  <th className="text-left py-2">日期</th>
                  <th className="text-left py-2">姓名</th>
                  <th className="text-left py-2">上班</th>
                  <th className="text-left py-2">下班</th>
                  <th className="text-left py-2">工時</th>
                  <th className="text-left py-2">狀態</th>
                </tr>
              </thead>
              <tbody>
                <tr className="border-b">
                  <td className="py-2">2026-04-01</td>
                  <td className="py-2">陳大文</td>
                  <td className="py-2">09:00</td>
                  <td className="py-2">18:00</td>
                  <td className="py-2">9 小時</td>
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

export default Reports;
