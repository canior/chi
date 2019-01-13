// pages/user/trainee/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    menu: [
      { name: '好友贡献', isValid: null },
      { name: '活跃', isValid: true },
      { name: '失效', isValid: false }
    ],
    isValid: null,
    isLogin: null,
    user: null,
    rewardList: [],
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

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
  },

  //转我的学员列表
  toMyTraineeList: function () {
    wx.navigateTo({
      url: '/pages/user/trainee/list',
    })
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