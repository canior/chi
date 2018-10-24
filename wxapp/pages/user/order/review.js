// pages/user/order/review.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    rate: 3,
    review: '',
    tmpImageFilePaths: [],
    fileId: null,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {

  },

  // 提交
  submit: function () {

    wx.navigateBack({
    })
  },

  // 评分
  rate: function (e) {
    var idx = e.currentTarget.dataset.idx;
    var val = e.currentTarget.dataset.val;
    var rate = this.data.rate;
    switch (idx) {
      case '1': rate = (val == 'on' ? 0 : 1); break;
      case '2': rate = (val == 'on' ? 1 : 2); break;
      case '3': rate = (val == 'on' ? 2 : 3); break;
      case '4': rate = (val == 'on' ? 3 : 4); break;
      case '5': rate = (val == 'on' ? 4 : 5); break;
    }
    this.setData({
      rate: rate
    })
  },

  // 评论
  review: function (e) {
    this.setData({
      review: e.detail.value
    })
  },

  // 上传图片
  upload: function () {
    const that = this;
    wx.chooseImage({
      count: 1,
      sizeType: ['original', 'compressed'],
      sourceType: ['album', 'camera'],
      success: function (res) {
        var tempFilePaths = res.tempFilePaths;
        that.setData({
          tmpImageFilePaths: tempFilePaths
        })
        //启动上传等待中...  
        wx.showLoading({
          title: '正在上传...',
          mask: true
        })
        wx.uploadFile({
          url: app.globalData.baseUrl + '/file/upload',
          filePath: tempFilePaths[0],
          name: 'file',
          formData: {
            'thirdSession': wx.getStorageSync('thirdSession'),
          },
          success: function (res) {
            if (res.statusCode == 200 && res.data.code == 200) {
              console.log(res.data.data)
            } else {
              console.log('wx.request return error', res.statusCode);
            }
          },
          fail: function (res) {
            wx.showModal({
              title: '错误提示',
              content: '上传图片失败',
              showCancel: false,
              success: function (res) { }
            })
          },
          complete: function (res) {
            wx.hideLoading();
          }
        })
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