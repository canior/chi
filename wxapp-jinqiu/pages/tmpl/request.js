/**
 * 确认选择收货地址
 * url: /wxapi/groupUserOrder/confirmAddress
 */
function confirmAddress(that, url, addressId) {
  const groupUserOrderId = that.data.groupUserOrder ? that.data.groupUserOrder.id : that.data.groupUserOrderId;
  wx.request({
    url: url,
    data: {
      thirdSession: wx.getStorageSync('thirdSession'),
      addressId: addressId,
      groupUserOrderId: groupUserOrderId,
    },
    method: 'POST',
    success: (res) => {
      if (res.statusCode == 200 && res.data.code == 200) {
        //console.log(res.data.data)
        if (that.data.groupUserOrder) {//来自pay,刷新数据
          that.setData({
            groupUserOrder: res.data.data.groupUserOrder
          })
        } else {//来自address,转回pay
          wx.redirectTo({
            url: '/pages/group/pay?orderId=' + groupUserOrderId,
          })
        }
      } else {
        console.log('wx.request return error', res.statusCode);
      }
    },
    fail(e) {
    },
    complete(e) { }
  })
}

// 导入
function importAddress(that, addressInfo) {
  if (addressInfo) {
    that.setData({
      ['address.name']: addressInfo.userName,
      ['address.phone']: addressInfo.telNumber,
      ['address.region']: [addressInfo.provinceName, addressInfo.cityName, addressInfo.countyName],
      ['address.regionText']: addressInfo.provinceName + ' ' + addressInfo.cityName + ' ' + addressInfo.countyName,
      ['address.address']: addressInfo.detailInfo
    })
  }
}

// 保存
// wxAddress = true, 表示地址列表页直接保存wxAddress导入地址
function saveAddress(that, baseUrl, wxAddress = false) {
  const address = that.data.address
  if (!__validation(address, wxAddress)) return;
  wx.request({
    url: baseUrl + '/user/address/post',
    data: {
      userAddressId: address.id ? address.id : null,
      name: address.name,
      phone: address.phone,
      province: address.region[0],
      city: address.region[1],
      county: address.region[2],
      address: address.address,
      //isDefault: adress.setDefault, 
      thirdSession: wx.getStorageSync('thirdSession')
    },
    method: 'POST',
    success: (res) => {
      if (res.statusCode == 200 && res.data.code == 200) {
        //console.log(res.data.data)
        if (that.data.groupUserOrderId) {
          const url = baseUrl + '/groupUserOrder/confirmAddress';
          confirmAddress(that, url, res.data.data.userAddress.id)
        } else {
          wx.redirectTo({
            url: '/pages/user/address/index',
          })
        }
      } else {
        console.log('wx.request return error', res.statusCode);
      }
    },
    fail(e) {
    },
    complete(e) { }
  })
}

// 检查地址输入是否完整
function __validation(address, wxAddress) {
  if (!address.name) {
    wx.showModal({
      content: '请输入姓名',
      showCancel: false,
    });
    return false;
  }
  if (!address.phone) {
    wx.showModal({
      content: '请输入手机号码',
      showCancel: false,
    });
    return false;
  }  
  if (wxAddress) {
    //微信直接导入地址手机号已判断首位为1和位数为11
  } else if (!(/^\d{11}$/.test(address.phone))) {
    wx.showModal({
      content: '手机号码位数不对',
      showCancel: false,
    });
    return false;
  }
  if (address.region && address.region.length == 0) {
    wx.showModal({
      content: '请选择省市、区县',
      showCancel: false,
    });
    return false;
  }
  if (!address.address) {
    wx.showModal({
      content: '请输入详细地址',
      showCancel: false,
    });
    return false;
  }
  return true;
}

module.exports = {
  confirmAddress: confirmAddress,
  saveAddress: saveAddress,
  importAddress: importAddress
}