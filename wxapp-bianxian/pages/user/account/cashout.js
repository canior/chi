// pages/user/account/cashout.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    userAccount: null,
    amount: null
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getMyAccount()
  },

  getMyAccount: function () {
    const that = this;
    wx.showLoading({
      title: '载入中',
    })
    wx.request({
      url: app.globalData.baseUrl + '/user/account/view',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            userAccount: res.data.data
          })
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) { },
      complete(e) {
        wx.hideLoading()
      }
    })
  },

  inputAmount: function (e) {
    this.setData({
      amount: e.detail.value
    })
  },

  submit: function (e) {
    const that = this;
    const amount = this.data.amount;
    if (!this.validation(amount)) return;
    wx.request({
      url: app.globalData.baseUrl + '/user/account/withdraw',
      data: {
        amount: this.data.amount,
        thirdSession: wx.getStorageSync('thirdSession')
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          wx.navigateBack({})
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) { },
      complete(e) { }
    })
  },

  validation: function (amount) {
    if (!(/^\d+(\.\d+)?$/.test(amount))) {
      wx.showModal({
        content: '提现金额有误',
        showCancel: false,
      });
      return false;
    }
    if (amount > this.data.userAccount.balance) {
      wx.showModal({
        content: '提现金额超出最大可提现金额',
        showCancel: false,
      });
      return false;
    }
    return true;
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