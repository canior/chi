// pages/user/address/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    addresses: [],
    delBtnWidth: 80 //删除按钮宽度(px)  
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    this.getAddresses()
  },

  getAddresses: function() {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/addresses',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          //console.log(res.data.data)
          that.setData({
            addresses: res.data.data.userAddresses
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

  editAddress: function(e) {
    const id = e.currentTarget.dataset.id ? e.currentTarget.dataset.id : '';
    wx.redirectTo({
      url: '/pages/user/address/edit?id=' + id,
    })
  },

  delAddress: function(e) {
    const id = e.currentTarget.dataset.id;
    const that = this;
    wx.showModal({
      title: '提示',
      content: '您是否确认要删除所选择的收货地址？',
      confirmText: '是',
      cancelText: '否',
      success: function (res) {
        if (res.confirm) {
          wx.request({
            url: app.globalData.baseUrl + '/user/address/delete',
            data: {
              thirdSession: wx.getStorageSync('thirdSession'),
              userAddressId: id
            },
            method: 'POST',
            success: (res) => {
              if (res.statusCode == 200 && res.data.code == 200) {
                //console.log(res.data.data)
                that.getAddresses()
              } else {
                console.log('wx.request return error', res.statusCode);
              }
            },
            fail(e) {
            },
            complete(e) { }
          })
        }
      }
    })
  },

  // 左滑开始
  touchS: function (e) {
    //console.log("touchS", e);
    if (e.touches.length == 1) {
      this.setData({
        startX: e.touches[0].clientX
      });
    }
  },
  // 左滑移动
  touchM: function (e) {
    //console.log("touchM", e);
    var that = this
    if (e.touches.length == 1) {
      var moveX = e.touches[0].clientX;
      var disX = that.data.startX - moveX;// 计算滑动距离
      var delBtnWidth = that.data.delBtnWidth;
      var txtStyle = "";
      if (disX == 0 || disX < 0) {
        txtStyle = "left:0px";
      } else if (disX > 0) {
        txtStyle = "left:-" + disX + "px";
        if (disX >= delBtnWidth) {
          txtStyle = "left:-" + delBtnWidth + "px";
        }
      }
      var index = e.currentTarget.dataset.index;
      var list = that.data.addresses;
      list[index].txtStyle = txtStyle;
      this.setData({
        addresses: list
      });
    }
  },
  touchE: function (e) {
    //console.log("touchE", e);
    var that = this
    if (e.changedTouches.length == 1) {
      var endX = e.changedTouches[0].clientX;
      var disX = that.data.startX - endX;
      var delBtnWidth = that.data.delBtnWidth;
      //若滑动距离大于删除按钮的1/2，则显示删除按钮
      var txtStyle = disX > delBtnWidth / 2 ? "left:-" + delBtnWidth + "px" : "left:0px";
      var index = e.currentTarget.dataset.index;
      var list = that.data.addresses;
      list[index].txtStyle = txtStyle;
      that.setData({
        addresses: list
      });
    }
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