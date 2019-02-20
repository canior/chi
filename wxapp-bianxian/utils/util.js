const formatTime = date => {
  const year = date.getFullYear()
  const month = date.getMonth() + 1
  const day = date.getDate()
  const hour = date.getHours()
  const minute = date.getMinutes()
  const second = date.getSeconds()

  return [year, month, day].map(formatNumber).join('/') + ' ' + [hour, minute, second].map(formatNumber).join(':')
}

const formatNumber = n => {
  n = n.toString()
  return n[1] ? n : '0' + n
}

const getQueryVariable = (url, variable) => {
  var vars = url.split("&");
  for (var i = 0; i < vars.length; i++) {
    var pair = vars[i].split("=");
    if (pair[0] == variable) { return pair[1]; }
  }
  return (false);
}

// 判断空数组或空对象
const isEmpty = (ret) => {
  return (Array.isArray(ret) && ret.length === 0) || (Object.prototype.isPrototypeOf(ret) && Object.keys(ret).length === 0);
}

module.exports = {
  formatTime: formatTime,
  getQueryVariable: getQueryVariable,
  isEmpty: isEmpty
}
