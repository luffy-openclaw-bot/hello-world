import { useState, useEffect } from 'react';
import { useNavigate } from 'react-router-dom';

const API_URL = 'http://localhost:8080/backend/api';

function Checkin() {
  const navigate = useNavigate();
  const [location, setLocation] = useState(null);
  const [loading, setLoading] = useState(false);
  const [message, setMessage] = useState('');
  const [user, setUser] = useState(null);

  useEffect(() => {
    const userData = localStorage.getItem('user');
    if (userData) {
      setUser(JSON.parse(userData));
    }
    
    // 獲取 GPS 位置
    if ('geolocation' in navigator) {
      navigator.geolocation.getCurrentPosition(
        (position) => {
          setLocation({
            latitude: position.coords.latitude,
            longitude: position.coords.longitude
          });
        },
        (error) => {
          setMessage('無法獲取位置，請允許 GPS 權限');
        }
      );
    }
  }, []);

  const handleCheckin = async (type) => {
    if (!location) {
      setMessage('請等待 GPS 定位完成');
      return;
    }

    setLoading(true);
    const token = localStorage.getItem('token');

    try {
      const response = await fetch(`${API_URL}/checkin.php`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'Authorization': `Bearer ${token}`
        },
        body: new URLSearchParams({
          latitude: location.latitude,
          longitude: location.longitude,
          type: type
        })
      });

      const data = await response.json();

      if (data.status === 'success') {
        setMessage(`${type === 'checkin' ? '上班' : '下班'}打卡成功！`);
      } else {
        setMessage(data.message);
      }
    } catch (err) {
      setMessage('網絡錯誤，請重試');
    } finally {
      setLoading(false);
    }
  };

  const handleLogout = () => {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    navigate('/login');
  };

  return (
    <div className="min-h-screen bg-gray-50">
      {/* Header */}
      <header className="bg-white shadow">
        <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8 flex justify-between items-center">
          <h1 className="text-2xl font-bold text-gray-900">📍 GPS 打卡</h1>
          <button
            onClick={handleLogout}
            className="text-gray-600 hover:text-gray-900"
          >
            登出
          </button>
        </div>
      </header>

      {/* Main Content */}
      <main className="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <div className="px-4 py-6 sm:px-0">
          {/* User Info */}
          <div className="bg-white rounded-lg shadow p-6 mb-6">
            <h2 className="text-lg font-bold text-gray-900 mb-2">
              歡迎，{user?.name || '用戶'}
            </h2>
            <p className="text-gray-600">{user?.email}</p>
          </div>

          {/* Location Info */}
          <div className="bg-white rounded-lg shadow p-6 mb-6">
            <h3 className="text-lg font-bold text-gray-900 mb-4">📍 當前位置</h3>
            {location ? (
              <div className="space-y-2">
                <p className="text-gray-700">
                  <span className="font-bold">緯度：</span>{location.latitude.toFixed(6)}
                </p>
                <p className="text-gray-700">
                  <span className="font-bold">經度：</span>{location.longitude.toFixed(6)}
                </p>
                <p className="text-green-600">✅ GPS 定位成功</p>
              </div>
            ) : (
              <div className="flex items-center space-x-2 text-gray-600">
                <div className="animate-spin rounded-full h-5 w-5 border-b-2 border-blue-600"></div>
                <span>獲取 GPS 位置中...</span>
              </div>
            )}
          </div>

          {/* Checkin Buttons */}
          <div className="bg-white rounded-lg shadow p-6">
            <h3 className="text-lg font-bold text-gray-900 mb-4">打卡操作</h3>
            
            {message && (
              <div className={`p-4 rounded mb-4 ${message.includes('成功') ? 'bg-green-50 text-green-700' : 'bg-red-50 text-red-700'}`}>
                {message}
              </div>
            )}

            <div className="grid grid-cols-2 gap-4">
              <button
                onClick={() => handleCheckin('checkin')}
                disabled={loading || !location}
                className="bg-green-600 text-white py-4 px-6 rounded-lg font-bold hover:bg-green-700 transition disabled:opacity-50"
              >
                🌅 上班打卡
              </button>
              
              <button
                onClick={() => handleCheckin('checkout')}
                disabled={loading || !location}
                className="bg-blue-600 text-white py-4 px-6 rounded-lg font-bold hover:bg-blue-700 transition disabled:opacity-50"
              >
                🌆 下班打卡
              </button>
            </div>
          </div>
        </div>
      </main>
    </div>
  );
}

export default Checkin;
