// pages/user/course/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    menu: [
      { name: '全部', status: null },
      { name: '待注册', status: 'created' },
      { name: '已注册', status: 'delivered' },
      { name: '已取消', status: 'cancelled' },
    ],
    curStatus: null,
    myCourses: [],
    imgUrlPrefix: app.globalData.imgUrlPrefix,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    //app.buriedPoint(options)
    this.setData({
      curStatus: options.status ? options.status : null
    })
  },

  getMyCourses: function (status) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/groupUserOrders/',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        groupUserOrderStatus: status
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            myCourses: res.data.data.groupUserOrders,
            curStatus: status
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

  tapMenu: function (e) {
    this.getMyCourses(e.currentTarget.dataset.status)
  },

  // 转课程日志
  toMyCourseLog: function (e) {
    const orderId = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '/pages/user/course/log?id=' + orderId,
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
    this.getMyCourses(this.data.curStatus)
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