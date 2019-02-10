// pages/user/course/log.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    options: null,
    course: null,
    courseStudents: null,
    imgUrlPrefix: app.globalData.imgUrlPrefix,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    //app.buriedPoint(options)
    this.setData({
      options: options
    })
  },

  getMyCourseLog: function (id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/groupUserOrder/view',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        groupUserOrderId: id,
        url: '/pages/course/detail?id=' + id
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            course: res.data.data.groupUserOrder,
            courseStudents: res.data.data.courseStudents
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

  // 评价
  toUserComment: function (e) {
    wx.navigateTo({
      url: '/pages/user/course/review?id=' + this.data.course.id,
    })
  },

  // 分享
  toProductDetail: function (e) {
    wx.reLaunch({
      url: '/pages/course/detail?id=' + this.data.course.product.id,
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
    this.getMyCourseLog(this.data.options.id)
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