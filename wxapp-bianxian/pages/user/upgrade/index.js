// pages/user/upgrade/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    userLevels: [
      { key: 'ADVANCED', name: '高级学员', show: true, enable: true },
      { key: 'PARTNER', name: '合伙人', show: true, enable: true }
    ],
    userLevel: null,
    upgradeUserOrder: null,
    btnText: '申请'
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
  },

  getUpgradeUserOrder: function () {
    const that = this;
    wx.showLoading({
      title: '载入中',
    })
    wx.request({
      url: app.globalData.baseUrl + '/user/upgradeUserOrder/view',
      data: {
        thirdSession: wx.getStorageSync('thirdSession')
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          const user = res.data.data.user;
          let userLevels = this.data.userLevels;
          let upgradeUserOrder = res.data.data.upgradeUserOrder
          let userLevel = null;
          let btnText = '申请';
          if (user.userLevel == '普通学员') {
            userLevel = upgradeUserOrder ? upgradeUserOrder.userLevel : null;
            userLevels = [
              { key: 'ADVANCED', name: '高级学员', show: true, enable: userLevel == 'ADVANCED' },
              { key: 'PARTNER', name: '合伙人', show: true, enable: userLevel == 'PARTNER' }
            ];
            btnText = upgradeUserOrder ? upgradeUserOrder.statusText : '申请'
          }
          else {// 高级学员
            userLevel = upgradeUserOrder ? upgradeUserOrder.userLevel : null;
            userLevels = [
              { key: 'ADVANCED', name: '高级学员', show: false, enable: false },
              { key: 'PARTNER', name: '合伙人', show: true, enable: userLevel == 'PARTNER' }
            ];
            btnText = upgradeUserOrder ? upgradeUserOrder.statusText : '申请'
          }
          that.setData({
            upgradeUserOrder: upgradeUserOrder,
            user: user,
            userLevel: userLevel,
            userLevels: userLevels,
            btnText: btnText
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

  // 选择升级通道
  tapFilter: function (e) {
    const key = e.currentTarget.dataset.key;
    this.setData({
      userLevel: key
    })
  },

  // 申请升级
  tapApply: function (e) {
    const that = this;
    if (this.data.btnText != '申请') return;
    if (this.data.userLevel) {
      wx.showModal({
        title: '提示',
        content: '您是否确认要升级到' + (this.data.userLevel == 'PARTNER' ? '合伙人' : '高级学员'),
        confirmText: '是',
        cancelText: '否',
        success: function (res) {
          if (res.confirm) {
            that.submit();
          }
        }
      })
    }
    else {
      wx.showModal({
        content: '请选择升级目标',
        showCancel: false,
      });      
    }
  },

  // 提交申请
  submit: function(e) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/upgradeUserOrder/create',
      data: {
        userLevel: that.data.userLevel,
        thirdSession: wx.getStorageSync('thirdSession')
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          this.onShow();
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) { },
      complete(e) { }
    })
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
    this.getUpgradeUserOrder()
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