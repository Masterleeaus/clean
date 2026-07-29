import api from "../api";

class AdminSupportService {
  getTickets(sort, offset) {
    return api.get(`admin/support/tickets?sort=${sort}&offset=${offset}`, {});
  }

  getUserTickets(user_id, offset) {
    return api.get(`admin/support/user_tickets?user_id=${user_id}&offset=${offset}`, {});
  }

  getTicket(uid) {
    return api.get(`admin/support/ticket?uid=${uid}`, {});
  }

  createComment(uid, message) {
    return api.post(`admin/support/create_comment?uid=${uid}`, {
      message: message
    });
  }

  switchTicketStatus(uid) {
    return api.post(`admin/support/change_status?uid=${uid}`, {});
  }
}

export default new AdminSupportService();
