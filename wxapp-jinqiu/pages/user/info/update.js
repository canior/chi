// pages/user/info/edit.js
const app= getApp()
Page({
  /**
   * 页面的初始数据
   */
  data: {
    user: {
      id: '',
      name: '',
      phone: '',
      idNum: '',
      company: '',
      wechat: '',
      recommanderName: '',
    },
    groupUserOrderId: null, //从支付因个人资料不完整而转来
    upgrade: false, //从学员升级因个人资料不完整而来
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    app.buriedPoint(options)
    if (options.orderId) {//从支付因个人资料不完整而转来
      this.setData({
        groupUserOrderId: options.orderId
      })
    } else if (options.upgrade) {//从学员升级因个人资料不完整而来
      this.setData({
        upgrade: true
      })
    }
    this.getUserInfo();
    wx.setNavigationBarTitle({ title: '个人资料' })
  },

  // 获取个人资料
  getUserInfo: function() {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/personal/view',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          const user = res.data.data.user
          that.setData({
            ['user.id']: user.id,
            ['user.name']: user.name,
            ['user.phone']: user.phone,
            ['user.idNum']: user.idNum,
            ['user.company']: user.company,
            ['user.wechat']: user.wechat,
            ['user.recommanderName']: user.recommanderName,
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

  inputName: function(e) {
    this.setData({
      ['user.name']: e.detail.value
    })
  },

  inputPhone: function (e) {
    this.setData({
      ['user.phone']: e.detail.value
    })
  },

  inputIdNum: function (e) {
    this.setData({
      ['user.idNum']: e.detail.value
    })
  },

  inputCompany: function (e) {
    this.setData({
      ['user.company']: e.detail.value
    })
  },

  inputWechat: function (e) {
    this.setData({
      ['user.wechat']: e.detail.value
    })
  },

  inputRefereeName: function (e) {
    this.setData({
      ['user.recommanderName']: e.detail.value
    })
  },

  // 保存
  save: function (e) {
    const that = this;
    const user = this.data.user;
    if(!this.validation(user)) return;
    wx.request({
      url: app.globalData.baseUrl + '/user/personal/update',
      data: {
        name: user.name,
        phone: user.phone,
        idNum: user.idNum,
        company: user.company,
        wechat: user.wechat,
        recommanderName: user.recommanderName,
        thirdSession: wx.getStorageSync('thirdSession')
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          if (that.data.groupUserOrderId) {
            wx.redirectTo({
              url: '/pages/course/pay?orderId=' + that.data.groupUserOrderId,
            })
          } else if (that.data.upgrade) {
            wx.redirectTo({
              url: '/pages/user/upgrade/index',
            })
          } else {
            wx.navigateBack({
            })
          }
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) { },
      complete(e) { }
    })
  },

  // 检查输入是否完整
  validation: function (user) {
    if(!user.name) {
      wx.showModal({
        content: '请输入姓名',
        showCancel: false,
      });
      return false;
    }
    if(!user.phone) {
      wx.showModal({
        content: '请输入手机号码',
        showCancel: false,
      });
      return false;
    }
    if(!(/^1[34578]\d{9}$/.test(user.phone))) {
      wx.showModal({
        content: '手机号码有误',
        showCancel: false,
      });
      return false;
    }
    /*if (!user.idNum) {
      wx.showModal({
        content: '请输入身份证号码',
        showCancel: false,
      });
      return false;
    }
    if (!(/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/.test(user.idNum))) {
      wx.showModal({
        content: '身份证号码有误',
        showCancel: false,
      });
      return false;
    }*/
    return true;
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