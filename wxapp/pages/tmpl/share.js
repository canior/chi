var __data = {
  showModal: false,
  shareSources: [],  
}

function init(that) {
  hideModal(that)
}

function showModal(that) {
  __data.showModal = true;
  that.setData({
    shareData: __data
  });
}

function hideModal(that) {
  __data.showModal = false;
  that.setData({
    shareData: __data
  });
}

function setShareSources(that, shareSources) {
  __data.shareSources = shareSources;
  that.setData({
    shareData: __data
  });  
}

function saveShareSource(that, e, url) {
  __saveShareSource(that, url, e.currentTarget.dataset.type)
}

function __saveShareSource(that, url, shareSourceType) {
  const pages = getCurrentPages();
  const currentPageUrl = pages[pages.length - 1].route;
  const shareSource = __data.shareSources.find(item => { return item.type == shareSourceType});
  wx.request({
    url: url,
    data: {
      thirdSession: wx.getStorageSync('thirdSession'),
      url: currentPageUrl,
      shareSourceType: shareSourceType,
      shareSourceId: shareSource.id,
      title: shareSource.title,
      bannerFileId: shareSource.bannerFileId,
      productId: that.data.product ? that.data.product.id : null
    },
    method: 'POST',
    success: (res) => {
      if (res.statusCode == 200 && res.data.code == 200) {
        console.log(res.data.data);
      } else {
        console.log('wx.request return error', res.statusCode);
      }
    },
    fail(e) {},
    complete(e) {}
  })
}

function shareObject(that, res) {
  const shareSourceType = res.target.dataset.type;
  const shareSource = __data.shareSources.find(item => { return item.type == shareSourceType });
  return {
    title: shareSource.title,
    imageUrl: that.data.imgUrlPrefix + '/' + shareSource.bannerFileId,
    path: shareSource.page
  }
}

module.exports = {
  init: init,
  showModal: showModal,
  hideModal: hideModal,
  setShareSources: setShareSources,
  saveShareSource: saveShareSource,
  shareObject: shareObject
}