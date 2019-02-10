// pages/user/student/list.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    options: null,
    course: null,
    students: [],
    imgUrlPrefix: app.globalData.imgUrlPrefix
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

  getStudentList: function (id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/teacher/course/student',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        courseId: id,
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            course: res.data.data.course,
            students: res.data.data.students
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

  /**
   * 生命周期函数--监听页面初次渲染完成
   */
  onReady: function () {

  },

  /**
   * 生命周期函数--监听页面显示
   */
  onShow: function () {
    this.getStudentList(this.data.options.id)
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