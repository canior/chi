// pages/user/referral/index.js
const app = getApp()
const share = require('../../tmpl/share.js');
Page({

  /**
   * 页面的初始数据
   */
  data: {
    shareUser: null,
    isLogin: null,
    user: null,
    shareData: {},
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    //app.buriedPoint(options)
    this.setData({
      userLevel: options.level ? options.level : null
    })
  },

  getReferral: function (level) {
    const that = this;
    const pages = getCurrentPages();
    const currentPageUrl = '/' + pages[pages.length - 1].route;
    wx.showLoading({
      title: '载入中',
    })
    wx.request({
      url: app.globalData.baseUrl + '/user/shareUser',
      data: {
        userLevel: level,
        url: currentPageUrl,
        //page
        thirdSession: wx.getStorageSync('thirdSession'),
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            shareUser: res.data.data,
            userLevel: level
          })
          share.setShareSources(that, res.data.data.shareSources)
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

  // 邀请好友
  wxSaveShareSource: function (e) {
    share.saveShareSource(this, e, app.globalData.baseUrl + '/user/shareSource/create')
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
      isLogin: app.globalData.isLogin,
      user: app.globalData.user
    })
    if (this.data.isLogin) {
      this.getReferral(this.data.userLevel)
      share.init(this)
    }
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
  onShareAppMessage: function (res) {
    return share.shareObject(this, res)
  }
})