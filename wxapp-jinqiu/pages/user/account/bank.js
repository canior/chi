// pages/user/account/bank.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    user: {
      bank: '',
      bankAccountNumber: '',
      bankAccountName: '',
    },
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    wx.hideShareMenu()
    app.buriedPoint(options)
    this.getUserInfo();
    wx.setNavigationBarTitle({ title: '收款人账户信息' })
  },

  // 获取个人资料
  getUserInfo: function () {
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
            ['user.bank']: user.bank,
            ['user.bankAccountNumber']: user.bankAccountNumber,
            ['user.bankAccountName']: user.bankAccountName,
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

  inputBank: function (e) {
    this.setData({
      ['user.bank']: e.detail.value
    })
  },

  inputBankAccountNumber: function (e) {
    this.setData({
      ['user.bankAccountNumber']: e.detail.value
    })
  },

  inputBankAccountName: function (e) {
    this.setData({
      ['user.bankAccountName']: e.detail.value
    })
  },

  // 保存
  save: function (e) {
    const that = this;
    const user = this.data.user;
    if (!this.validation(user)) return;
    wx.request({
      url: app.globalData.baseUrl + '/user/bank/update',
      data: {
        bank: user.bank,
        bankAccountNumber: user.bankAccountNumber,
        bankAccountName: user.bankAccountName,
        thirdSession: wx.getStorageSync('thirdSession')
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          app.globalData.user = res.data.data.user;
          wx.redirectTo({
            url: '/pages/user/account/cashout',
          })
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
    if (!user.bank) {
      wx.showModal({
        content: '请输入开户银行，如招商银行',
        showCancel: false,
      });
      return false;
    }
    if (!user.bankAccountNumber) {
      wx.showModal({
        content: '请输入收款人账号',
        showCancel: false,
      });
      return false;
    }
    if (!user.bankAccountName) {
      wx.showModal({
        content: '请输入收款人名称',
        showCancel: false,
      });
      return false;
    }
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