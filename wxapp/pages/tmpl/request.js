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
        wx.redirectTo({
          url: '/pages/group/pay?orderId=' + groupUserOrderId,
        })
      } else {
        console.log('wx.request return error', res.statusCode);
      }
    },
    fail(e) {
    },
    complete(e) { }
  })
}

module.exports = {
  confirmAddress: confirmAddress
}