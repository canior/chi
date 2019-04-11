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

    //

    currentResource: '',
    multiListShow: false,
    rateShow: false,
    currentRate: '1.0',
    videoPlaying: false,
    controlHidden: true,
    currentTime: 0,
    isSwitchDefinition: false,
    currentVideoId: '',
    currentPoster: '',
    currentVideoTitle: '',
    currentVideoResource: [],
    currentDefinition: '',
    isAndroid: false,
    fullScreenData: "",
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
  },

  getVideo: function (id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/signInCourse',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        productId: id,
        url: '/pages/course/video?id=' + id
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          var course = "https://outin-a7944acc383b11e9a86700163e1a625e.oss-cn-shanghai.aliyuncs.com/af1a8bb7780f498d9271f81e9ac635e4/5509478f319b4d65acc3ce13460d85b7-8bbdd78434fd4df70d1c2441fca70d4a-ld.mp4?Expires=1554978399&OSSAccessKeyId=LTAI8bKSZ6dKjf44&Signature=%2B80vJRKRkcAxrX41MDtWBy7csZY%3D"
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

  //阿里点播
  videoPlayHandle (e) {
    this.data.videoPlaying = true
    this.setData({
      controlHidden: false,
      multiListShow: false
    })
    this.videoContext.playbackRate(Number(this.data.currentRate))
    if (this.data.isSwitchDefinition) {
      this.videoContext.seek(this.data.currentTime)
      this.data.isSwitchDefinition = false
    }

  },
  tapVideo(e) {
    this.setData({
      multiListShow: false,
      rateShow: false,
    })
    if (this.data.videoPlaying && !this.data.fullScreenData) {
      this.setData({
        controlHidden: !this.data.controlHidden
      })
    }
  },
  playPaused() {
    this.data.videoplaying = false
  },
  timeUpdate (e) {
    let { currentTime } = e.detail
    this.data.currentTime = currentTime
    this.data.videoplaying = true
  },
  // 视频缓冲触发事件
  videoWaiting () {
    this.setData({
      controlHidden: true
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