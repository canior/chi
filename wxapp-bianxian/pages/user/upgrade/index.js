// pages/user/upgrade/index.js
const app = getApp()
Page({

  /**
   * 页面的初始数据
   */
  data: {
    levels: [
      { key: 'ADVANCED', name: '高级学员', show: true, enable: true },
      { key: 'PARTNER', name: '合伙人', show: true, enable: true },
      { key: 'DISTRIBUTOR', name: '分院', show: false, enable: false },
    ],
    selected: null,
    upgradeUserOrder: null,
    btnText: '申请'
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
  },

  // 判断空数组或空对象
  isEmpty: function (ret) {
    return (Array.isArray(ret) && ret.length === 0) || (Object.prototype.isPrototypeOf(ret) && Object.keys(ret).length === 0);
  },

  // 获取已申请的订单
  getUpgradeUserOrder: function () {
    const that = this;
    wx.showLoading({
      title: '载入中',
    })
    wx.request({
      url: app.globalData.baseUrl + '/user/upgradeUserOrder/view',
      data: {
        thirdSession: wx.getStorageSync('thirdSession')
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          const user = res.data.data.user;
          const upgradeUserOrder = this.isEmpty(res.data.data.upgradeUserOrder) ? null : res.data.data.upgradeUserOrder;
          //是否已提交申请且尚未通过
          let selected = upgradeUserOrder && upgradeUserOrder.status != 'approved' ? upgradeUserOrder.userLevel : null;
          let btnText = upgradeUserOrder && upgradeUserOrder.status != 'approved' ? upgradeUserOrder.statusText : '申请';
          //可申请哪些等级
          let levels = this.data.levels;
          if (user.userLevel == 'VISITOR') {
            levels.forEach((item) => {
              item.enable = selected ? false : true
            })
          }
          else if (user.userLevel == 'ADVANCED') {
            levels.forEach((item) => {
              if (item.key == 'ADVANCED') item.show = false;
              item.enable = selected ? false : true
            })
          }
          else if (user.userLevel == 'PARTNER') {
            levels.forEach((item) => {
              if (item.key == 'ADVANCED' || item.key == 'PARTNER') item.show = false;
              item.enable = selected ? false : true
            })
          }
          that.setData({
            user: user,
            upgradeUserOrder: upgradeUserOrder,
            selected: selected,
            levels: levels,
            btnText: btnText
          })
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) { },
      complete(e) {
        wx.hideLoading()
      }
    })
  },

  // 选择升级通道
  tapFilter: function (e) {
    const key = e.currentTarget.dataset.key;
    this.setData({
      selected: key
    })
  },

  // 申请升级
  tapApply: function (e) {
    const that = this;
    if (this.data.btnText != '申请') return;
    if (this.data.selected) {
      let selectedName = '';
      this.data.levels.forEach((item) => { if (item.key == this.data.selected) selectedName = item.name});
      wx.showModal({
        title: '提示',
        content: '您是否确认要升级到' + selectedName,
        confirmText: '是',
        cancelText: '否',
        success: function (res) {
          if (res.confirm) {
            that.submit();
          }
        }
      })
    }
    else {
      wx.showModal({
        content: '请选择升级目标',
        showCancel: false,
      });      
    }
  },

  // 提交申请
  submit: function(e) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/upgradeUserOrder/create',
      data: {
        userLevel: that.data.selected,
        thirdSession: wx.getStorageSync('thirdSession')
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          this.onShow();
        } else {
          console.log('wx.request return error', res.statusCode);
        }
      },
      fail(e) { },
      complete(e) { }
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
    this.getUpgradeUserOrder()
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