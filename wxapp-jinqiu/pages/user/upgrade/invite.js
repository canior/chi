// pages/user/upgrade/invite.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    user: null,
    code: null,
    btnDisabled: false //防止连击button
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    app.buriedPoint(options)
  },

  inputCode: function (e) {
    this.setData({
      code: e.detail.value
    })
  },

  submit: function (e) {
    const that = this;
    const code = this.data.code;
    if (!this.validation(code)) return;
    wx.showLoading({
      title: '等待提交...',
      mask: true,
    });
    that.setData({ btnDisabled: true });
    wx.request({
      url: app.globalData.baseUrl + '/user/upgradeCoupon',
      data: {
        coupon: code,
        thirdSession: wx.getStorageSync('thirdSession')
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          wx.showModal({
            content: '您输入的升级码已提交',
            showCancel: false,
            success: function (res) {
              if (res.confirm) {
                wx.navigateBack({
                })
              }
            }
          });
        } else if (res.statusCode == 200 && res.data.code == 201) {
          wx.showModal({
            content: res.data.data.error,
            showCancel: false,
            success: function (res) {
            }
          });
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) { },
      complete(e) {
        wx.hideLoading();
        that.setData({ btnDisabled: false });
      }
    })
  },

  validation: function (code) {
    if (!code) {
      wx.showModal({
        content: '请输入升级码',
        showCancel: false,
      });
      return false;
    }
    if (!(/^[0-9a-zA-Z]+$/.test(code))) {
      wx.showModal({
        content: '升级码不对',
        showCancel: false,
      });
      return false;
    }
    return true;
  },

  formSubmit: function (e) {
    this.submit();
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.setData({
      btnDisabled: false,
      user: app.globalData.user
    })
  },

  /**
   * 生命周期函数--监听页面隐藏
   */
  onHide: function () {

  },

  /**
   * 生命周期函数--监听页面卸载
   */
  onUnload: function () {

  },

  /**
   * 页面相关事件处理函数--监听用户下拉动作
   */
  onPullDownRefresh: function () {

  },

  /**
   * 页面上拉触底事件的处理函数
   */
  onReachBottom: function () {

  },

  /**
   * 用户点击右上角分享
   */
  onShareAppMessage: function () {

  }
})