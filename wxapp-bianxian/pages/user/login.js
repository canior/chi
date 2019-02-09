// pages/user/login.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    canIUse: wx.canIUse('button.open-type.getUserInfo')
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    app.buriedPoint(options)
  },

  // bindgetuserinfo: 授权并登录
  getUserInfo: function (e) {
    let userInfo = e.detail.userInfo
    if (userInfo) {// 用户接受授权
      //console.log('login', e.detail.userInfo)
      app.globalData.userInfo = e.detail.userInfo;
      if (app.globalData.isLogin) {// 有thirdSession,登录成功
        wx.navigateBack()
      } else {// 无thirdSession,继续登录
        app.login(userInfo, function () {
          wx.navigateBack()
        });
      }
    } else {// 用户拒绝授权
      //console.log('login', '授权被拒绝');
      wx.showModal({
        title: '用户未授权',
        content: '如需使用全部功能，请重新登录',
        showCancel: false,
        success: function (res) {
          if (res.confirm) {
          }
        }
      })
    }
  },

  // bindtap: 微信版本低于1.3.0
  doLogin: function (e) {
    wx.getSystemInfo({
      success: function (res) {
        app.debug('login', 'wx.version_low', res);
      }
    })
    wx.showModal({
      title: '提示',
      content: '您的微信版本过低，请升级后重新进入。',
      showCancel: false,
      success: function (res) {
        if (res.confirm) {
        }
      }
    })
  },

  // 手机登录
  tapMobileLogin: function (e) {
    let userInfo = e.detail.userInfo
    if (userInfo) {// 用户接受授权
      //console.log('login', e.detail.userInfo)
      app.globalData.userInfo = e.detail.userInfo;
      const pages = getCurrentPages();
      const prevPageUrl = '/' + pages[pages.length - 2].route;
      console.log('prevPageUrl', prevPageUrl)
      if (app.globalData.isLogin) {// 有thirdSession,登录成功
        //wx.navigateBack()
        wx.navigateTo({
          url: '/pages/user/mobile/login?retUrl=' + prevPageUrl,
        })
      } else {// 无thirdSession,继续登录
        app.login(userInfo, function () {
          //wx.navigateBack()
          wx.navigateTo({
            url: '/pages/user/mobile/login?retUrl=' + prevPageUrl,
          })          
        });
      }
    } else {// 用户拒绝授权
      //console.log('login', '授权被拒绝');
      wx.showModal({
        title: '用户未授权',
        content: '如需使用全部功能，请重新登录',
        showCancel: false,
        success: function (res) {
          if (res.confirm) {
          }
        }
      })
    }
  },

  // 返回首页
  toHome: function () {
    wx.switchTab({
      url: '/pages/course/index',
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