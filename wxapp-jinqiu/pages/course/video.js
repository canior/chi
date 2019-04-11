// pages/course/video.js
const app = getApp()
const courseReview = require('../tmpl/courseReview.js');
const share = require('../tmpl/share.js');
const bottom = require('../tmpl/bottom.js');
const util = require('../../utils/util.js');
Page({

  /**
   * 页面的初始数据
   */
  inputValue: '',
  data: {
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    course: null,
    courseReviewData: {},
    bottomData: {},
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    if (options.title) wx.setNavigationBarTitle({ title: options.title })
    //options.scene = encodeURIComponent('ss=123&p=456&go=789');
    const productId = options.id ? options.id : app.parseScene(options, 'p')
    this.getVideo(productId)
    const url = app.globalData.baseUrl + '/courses/' + productId + '/reviews'
    courseReview.init(this, url);
    app.buriedPoint(options)
    const that = this;
    app.userActivityCallback = res => {
      that.getVideo(productId);
      app.buriedPoint(options)
    }
  },

  getVideo: function (id) {
    const that = this;
    const thirdSession = wx.getStorageSync('thirdSession');
    if (!thirdSession) return;
    wx.request({
      url: app.globalData.baseUrl + '/user/signInCourse',
      data: {
        thirdSession: thirdSession,
        productId: id,
        url: '/pages/course/video?id=' + id
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          var course = res.data.data.course
          that.setData({
            course: course
          })
          share.setShareSources(that, res.data.data.shareSources)
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {
      },
      complete(e) { }
    })
  },

  wxReview: function () {
    wx.navigateTo({
      url: '/pages/user/course/review?id=' + this.data.course.productId,
    })
  },

  wxHome: function (e) {
    wx.switchTab({
      url: '/pages/course/index',
    })
  },

  // 分享:邀请好友
  wxShowShareModal: function (e) {
    share.showModal(this)
  },
  wxHideShareModal: function (e) {
    share.hideModal(this)
  },
  wxSaveShareSource: function (e) {
    share.saveShareSource(this, e, app.globalData.baseUrl + '/user/shareSource/create')
  },

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {
    this.videoContext = wx.createVideoContext('myVideo')
  },

  bindInputBlur: function (e) {
    this.inputValue = e.detail.value
  },
  bindSendDanmu: function () {
    this.videoContext.sendDanmu({
      text: this.inputValue,
      color: util.getRandomColor()
    })
  },
  bindPlay: function () {
    this.videoContext.play()
  },
  bindPause: function () {
    this.videoContext.pause()
  },
  videoErrorCallback: function (e) {
    console.log('视频错误信息:', e.detail.errMsg)
  },  

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    bottom.init(this)
    share.init(this)
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