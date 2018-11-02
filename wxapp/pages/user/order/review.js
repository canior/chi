// pages/user/order/review.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    groupUserOrder: null,
    imgUrlPrefix: app.globalData.imgUrlPrefix,    
    rate: 0,
    review: '',
    uploadStack: [
      { tmpImageFilePath: '', fileId: null },
      { tmpImageFilePath: '', fileId: null },
      { tmpImageFilePath: '', fileId: null },
    ],
    stackIndex: 0,
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.buriedPoint(options)
    this.getGroupUserOrder(options.id);
  },

  getGroupUserOrder: function (id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/groupUserOrder',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        groupUserOrderId: id
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          const groupUserOrder = res.data.data.groupUserOrder;
          var rate = 0, 
            review = '',
            uploadStack = [
              { tmpImageFilePath: '', fileId: null },
              { tmpImageFilePath: '', fileId: null },
              { tmpImageFilePath: '', fileId: null },
            ],
            stackIndex = 0;
          if (groupUserOrder.productReviews.length > 0) {
            const productReview = groupUserOrder.productReviews[0];
            rate = productReview.rate;
            review = productReview.review;
            productReview.productReviewImages.forEach((item, index) => {
              if (index < 3) {
                uploadStack[index] = {
                  tmpImageFilePath: that.data.imgUrlPrefix + '/' + item.fileId,
                  fileId: item.fileId
                }
                stackIndex = Math.max(stackIndex, index + 1)
              }
            })
          }
          that.setData({
            groupUserOrder: groupUserOrder,
            rate: rate,
            review: review,
            uploadStack: uploadStack,
            stackIndex: stackIndex
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

  // 提交
  submit: function () {
    const that = this;
    if (!that.validation()) return;
    var imageIds = [];
    that.data.uploadStack.forEach((item) => {
      if (item.fileId > 0) imageIds.push(item.fileId)
    })
    wx.request({
      url: app.globalData.baseUrl + '/user/groupUserOrder/review',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        groupUserOrderId: that.data.groupUserOrder.id,
        rate: that.data.rate,
        review: that.data.review,
        imageIds: imageIds
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          wx.navigateBack({
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

  // 检查输入是否完整
  validation: function () {
    if (!this.data.rate) {
      wx.showModal({
        content: '请给评分',
        showCancel: false,
      });
      return false;
    }
    return true;
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
  upload: function (e) {
    const that = this;
    const index = e.currentTarget.dataset.index;
    wx.chooseImage({
      count: 1,
      sizeType: ['original', 'compressed'],
      sourceType: ['album', 'camera'],
      success: function (res) {
        var tempFilePaths = res.tempFilePaths;
        //启动上传等待中...  
        wx.showLoading({
          title: '正在上传...',
          mask: true
        })
        wx.uploadFile({
          url: app.globalData.baseUrl + '/user/file/upload',
          filePath: tempFilePaths[0],
          name: 'file',
          formData: {
            'thirdSession': wx.getStorageSync('thirdSession'),
          },
          success: function (res) {
            if (res.statusCode == 200) {
              var data = JSON.parse(res.data);
              if (data.code == 200) {
                that.setData({
                  ['uploadStack['+index+']']: { tmpImageFilePath: tempFilePaths[0], fileId: data.data.fileId },
                  stackIndex: index + 1
                })
              } else {// 上传失败
                console.log('wx.uploadFile return error', res);
              }
            } else {// 上传失败
              console.log('wx.uploadFile return error', res);
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

  // 删除上传
  remove: function (e) {
    const index = e.currentTarget.dataset.index;
    var stack = this.data.uploadStack;
    for (let i = index+1; i < stack.length; i++) {
      stack[i-1] = stack[i];
    }
    stack[stack.length - 1] = { tmpImageFilePath: '', fileId: null };
    this.setData({
      uploadStack: stack,
      stackIndex: this.data.stackIndex - 1
    })
    console.log(stack, this.data)
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