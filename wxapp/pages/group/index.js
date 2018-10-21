// pages/group/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    showModal: false,
    imgUrlPrefix: app.globalData.imgUrlPrefix,    
    groupOrder: null,
    openUserId: null, //开团人（团长）
    userId: null,
    userInfo: {},
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    const id = options.id ? options.id : 10020;
    this.getGroupOrder(id);
    this.setData({
      userInfo: app.globalData.userInfo
    })
    app.userInfoReadyCallback = res => {
      this.setData({
        userInfo: app.globalData.userInfo
      })
    }
  },

  getGroupOrder: function(id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/groupOrder/view',
      data: {
        groupOrderId: id
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          that.setData({
            groupOrder: res.data.data.groupOrder
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

  showModal: function(e) {
    this.setData({
      showModal: true
    })
  },
  hideModal: function (e) {
    this.setData({
      showModal: false
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
    return {
      title: "分享标题",
      imageUrl: '',
      path: ''
    }
  }
})