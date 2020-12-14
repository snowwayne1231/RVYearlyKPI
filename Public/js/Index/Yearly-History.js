
function buildModal(el, data) {
  return new Vue({
      el: el,
      data: data,
      methods: {
          setData: function (data) { for (var i in data) { this._data[i] = data[i]; }; },
          rejectReport: function (id, reason) {
              generalRejectReport(id, reason);
              $(this.$el).modal('close');
          }
      }
  });
}

function initModal(from) {
  from.modal = {};
  from.modal.history = buildModal($('#HistoryModal')[0], {
      'historyRecords': []
  });

  $('.modal').modal();
  $('.tabs').tabs();
}