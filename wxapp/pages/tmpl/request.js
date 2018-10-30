/**
 * 确认选择收货地址
 * url: /wxapi/groupUserOrder/confirmAddress
 * id: 所选择的地址id
 */
function confirmAddress(that, url, id) {
  wx.request({
    url: url,
    data: {
      thirdSession: wx.getStorageSync('thirdSession'),
      addressId: id,
      groupUserOrderId: that.data.groupUserOrderId,
    },
    method: 'POST',
    success: (res) => {
      if (res.statusCode == 200 && res.data.code == 200) {
        //console.log(res.data.data)
        wx.redirectTo({
          url: '/pages/group/pay?orderId=' + that.data.groupUserOrderId,
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