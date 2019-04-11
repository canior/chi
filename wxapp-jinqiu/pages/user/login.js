// pages/user/login.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    bannerMeta: null,
    retUrl: null,
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    canIUse: wx.canIUse('button.open-type.getUserInfo')
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    this.getBanner()
    app.buriedPoint(options)
    this.setData({
      retUrl: options.retUrl ? decodeURIComponent(options.retUrl) : null
    })
  },

  // 获取banner
  getBanner: function () {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/preLogin',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            bannerMeta: res.data.data
          })
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {
      },
      complete(e) { }
    })
  },

  // Banner跳转
  redirect: function (e) {
    if (e.currentTarget.dataset.url) {
      wx.reLaunch({
        url: e.currentTarget.dataset.url,
      })
    }
  },
  
  // bindgetuserinfo: 授权并登录
  getUserInfo: function (e) {
    let userInfo = e.detail.userInfo
    if (userInfo) {// 用户接受授权
      //console.log('login', e.detail.userInfo)
      app.globalData.userInfo = e.detail.userInfo;
      if (app.globalData.isLogin) {// 有thirdSession,登录成功
        this.back()
      } else {// 无thirdSession,继续登录
        app.login(userInfo, function () {
          this.back()
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

  // 返回首页
  toHome: function () {
    wx.switchTab({
      url: '/pages/course/index',
    })
  },

  // 返回前页
  back: function () {
    const retUrl = this.data.retUrl;
    if (retUrl) {
      wx.redirectTo({ url: retUrl, })
    } else {
      wx.navigateBack()
    }
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