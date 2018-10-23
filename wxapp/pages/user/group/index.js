// pages/user/group/index.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    menu: ['我的拼团', '等待成团', '拼团成功', '拼团失败'],
    curIndex: 0,
    groupOrderList: [], 

  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  tapMenu: function (e) {
    this.setData({
      curIndex: e.currentTarget.dataset.index
    })
  },

  // 转拼团详情
  toGroupDetail: function (e) {
    const groupId = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '/pages/group/index?id=' + groupId,
    })
  },

  // 转订单详情
  toOrderDetail: function (e) {
    const orderId = e.currentTarget.dataset.id;
    wx.navigateTo({
      url: '/pages/user/order/detail?id=' + orderId,
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