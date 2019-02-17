var __data = {
  productReviews: [],  
  page: 1,
  limit: 20,
  hasMore: false,
  url: ''
}

function init(that, url) {
  __data.url = url;
  that.setData({
    productReviewData: __data
  });
  __getProductReview(that, url, __data.page)
}

function __getProductReview(that, url, page) {
  wx.showLoading({
    title: '玩命加载中',
  })
  wx.request({
    url: url,
    data: {
      page: page
    },
    success: (res) => {
      if (res.statusCode == 200 && res.data.code == 200) {
        console.log(res.data.data)
        var productReviews = __data.productReviews;
        productReviews.push(...res.data.data);
        __data.hasMore = res.data.data.length < __data.limit ? false : true;
        __data.page = __data.hasMore ? page + 1 : page;
        that.setData({
          productReviewData: __data
        })
      } else {
        console.log('wx.request return error', res.statusCode);
      }
    },
    fail(e) {
    },
    complete(e) {
      wx.hideLoading()
    }
  })
}

function getNextPage(that) {
  if (__data.hasMore) {
    __getProductReview(that, __data.url, __data.page)
  }
}

function previewImage(e, that) {
  const current = e.currentTarget.dataset.current
  const productReview = __data.productReviews.find(item => {
    return item.id = e.currentTarget.dataset.id
  })
  const urls = productReview.productReviewImages.map(item => {
    return that.data.imgUrlPrefix + '/' + item.fileId;
  });
  wx.previewImage({
    current: current,
    urls: urls
  })
}

module.exports = {
  init: init,
  getNextPage: getNextPage,
  previewImage: previewImage  
}