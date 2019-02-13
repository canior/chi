// pages/user/course/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    myCourses: [],
    imgUrlPrefix: app.globalData.imgUrlPrefix,
    isLogin: null,
    user: null,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    app.buriedPoint(options)
  },

  getMyCourses: function (status) {
    const that = this;
    wx.showLoading({
      title: '玩命加载中',
    })    
    wx.request({
      url: app.globalData.baseUrl + '/user/courses',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        page: 1
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            myCourses: res.data.data.courses
          })
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) {},
      complete(e) { wx.hideLoading() }
    })
  },

  // 转课程日志
  toCourseDetail: function (e) {
    const productId = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '/pages/course/detail?id=' + productId,
    })
  },

  wxReview: function (e) {
    const productId = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '/pages/user/course/review?id=' + productId,
    })
  },  

  // 发现更多课程
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
    this.setData({
      isLogin: app.globalData.isLogin,
      user: app.globalData.user
    })
    if (this.data.isLogin) {
      this.getMyCourses()
    } else {
      wx.navigateTo({
        url: '/pages/user/login',
      })
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
  onShareAppMessage: function () {

  }
})