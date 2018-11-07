// pages/user/address/edit.js
const app= getApp()
const request = require('../../tmpl/request.js')
Page({

  /**
   * 页面的初始数据
   */
  data: {
    address: {
      id: '',
      name: '',
      phone: '',
      region: [],
      regionText: null,
      customItem: '',
      address: '',
      isDefault: false,
      setDefault: false,
    },
    groupUserOrderId: null, //从支付页因无地址而转来
  },

  /**
   * 生命周期函数--监听页面加载
   */
  onLoad: function (options) {
    app.buriedPoint(options)
    if (options.orderId) {//从支付页因无地址而转来
      this.setData({
        groupUserOrderId: options.orderId
      })
    }
    if (options.id) {//编辑
      this.getAddress(options.id)
      wx.setNavigationBarTitle({ title: '编辑地址' })
    } else {//新建
      wx.setNavigationBarTitle({ title: '新建地址' })
    }
  },

  // 获取地址用于编辑
  getAddress: function(id) {
    const that = this;
    wx.request({
      url: app.globalData.baseUrl + '/user/address',
      data: {
        thirdSession: wx.getStorageSync('thirdSession'),
        userAddressId: id
      },
      method: 'POST',
      success: (res) => {
        if (res.statusCode == 200 && res.data.code == 200) {
          console.log(res.data.data)
          const userAddress = res.data.data.userAddresses
          that.setData({
            ['address.id']: userAddress.id,
            ['address.name']: userAddress.name,
            ['address.phone']: userAddress.phone,
            ['address.region']: [userAddress.region.province, userAddress.region.city, userAddress.region.county],
            ['address.regionText']: userAddress.region.province+' '+userAddress.region.city+' '+userAddress.region.county,
            ['address.address']: userAddress.address,
            ['address.isDefault']: userAddress.isDefault,
            ['address.setDefault']: userAddress.isDefault
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
      ['address.name']: e.detail.value
    })
  },

  inputPhone: function (e) {
    this.setData({
      ['address.phone']: e.detail.value
    })
  },

  inputAddress: function (e) {
    this.setData({
      ['address.address']: e.detail.value
    })
  },

  bindRegionChange: function (e) {
    const region = e.detail.value
    this.setData({
      ['address.region']: region,
      ['address.regionText']: region[0]+' '+region[1]+' '+region[2]
    })
  },

  setDefault: function(e) {
    this.setData({
      setDefault: !e.currentTarget.dataset.setdefault
    })
  },

  // 导入
  import: function (e) {
    const that = this;
    app.unifiedAuth(
      'scope.address',
      '需要使用您的通讯地址，是否允许？',
      function () {
        wx.chooseAddress({
          success: (res) => {
            console.log(res);
            request.importAddress(that, res)
          },
          fail: function (err) {
            console.log('wx.chooseAddress fail', err)
          }
        })
      }
    )
  },

  // 保存
  save: function (e) {
    request.saveAddress(this, app.globalData.baseUrl)
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