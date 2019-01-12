// pages/user/mobile/login.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    inputCellphone: '',
    inputSms: '',
    tip: '获取验证码',
    timer: null,
    retUrl: '',
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.buriedPoint();
    console.log(options)
    this.setData({
      retUrl: options.retUrl
    })
  },

  // 输入手机号码
  cellphoneInput: function (e) {
    this.setData({
      inputCellphone: e.detail.value
    })
  },
  // 输入短信验证码
  smsInput: function (e) {
    this.setData({
      inputSms: e.detail.value
    })
  },
  // 获取验证码
  sendSms: function () {
    var that = this;
    var timer = this.data.timer;
    if (!timer) {
      var time = 30;
      var tempTimer = setInterval(function () {
        if (time == 1) {
          that.setData({
            tip: '请重新获取',
            timer: null,
          })
          clearInterval(tempTimer);
        } else {
          time--;
          console.log(time);
          that.setData({
            tip: time + 's后重新获取',
          })
        }
      }, 1000);

      this.setData({
        timer: tempTimer
      })

      wx.request({
        url: app.globalData.baseUrl + '/sendSms',
        data: {
          cellphone: that.data.inputCellphone,
          thirdSession: wx.getStorageSync('thirdSession')
        },
        method: 'POST',
        success: (r) => {
          if (r.data.data.status == true) {
            wx.showToast({ title: '短信已发送' })
          } else if (r.data.msg === 'session_expired') {
            app.showToastJumpTo('请先登录', '/pages/login/login?r=using', 'redirect');
            return;
          } else if (r.data.msg === 'invalid_cellphone') {
            wx.showToast({ title: '手机号码不正确' });
          } else if (r.data.msg === 'request_frequency_exceeded') {
            wx.showToast({ title: '请稍后获取' });
          } else {
            wx.showToast({ title: '短信发送失败' })
          }
        },
        fail(e) {
          console.log('my', 'my_update_avatar_public_fail', e);
        }
      })
    }
  },

  // 手机登录
  toBindCellphone: function () {
    var that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/bindCellphone',
      data: {
        cellphone: that.data.inputCellphone,
        thirdSession: wx.getStorageSync('thirdSession'),
        code: that.data.inputSms
      },
      method: 'POST',
      success: (r) => {
        console.log(r);
        if (r.data.msg === 'cellphone_update_success') {
          wx.showToast({ title: '修改成功' });
          wx.reLaunch({ url: '/pages/mine/mine' });
        } else if (r.data.msg === 'session_expired') {
          app.showToastJumpTo('请先登录', '/pages/login/login?r=using', 'redirect');
          return;
        } else if (r.data.msg === 'invalid_cellphone') {
          wx.showToast({ title: '手机号码不正确' });
        } else if (r.data.msg === 'invalid_cellphone_code') {
          wx.showToast({ title: '验证码错误' });
        } else {
          wx.showToast({ title: '修改失败' });
        }
      },
      fail(e) {
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