var __data = {
  showModal: false,
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

module.exports = {
  init: init,
  showModal: showModal,
  hideModal: hideModal
}