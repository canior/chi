// pages/user/upgrade/index.js
Page({

  /**
   * 页面的初始数据
   */
  data: {
    uptext: '高级学员',
    btnText: '申请',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  // 申请升级
  tapApply: function (e) {
    const that = this;
    wx.showModal({
      title: '提示',
      content: '您是否确认要升级到'+this.data.uptext,
      confirmText: '是',
      cancelText: '否',
      success: function (res) {
        if (res.confirm) {
          that.setData({btnText:'已申请'});
          wx.showToast({
            title: '已成功提交申请',
            icon: 'success',
            duration: 2000
          })
        }
      }
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