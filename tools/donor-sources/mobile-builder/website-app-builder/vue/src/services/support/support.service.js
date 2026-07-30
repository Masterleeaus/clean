import api from "../api";

class SupportService {
  getTickets(sort, offset) {
    return api.get(`private/support/tickets?sort=${sort}&offset=${offset}`, {});
  }

  createTicket(title, message) {
    return api.post(`private/support/create`, {
      title: title,
      message: message
    });
  }

  getTicket(uid) {
    return api.get(`private/support/ticket?uid=${uid}`, {});
  }

  createComment(uid, message) {
    return api.post(`private/support/create_comment?uid=${uid}`, {
      message: message
    });
  }

  switchTicketStatus(uid) {
    return api.post(`private/support/change_status?uid=${uid}`, {});
  }

  setRating(uid, estimation) {
    return api.post(`private/support/change_rating?uid=${uid}`, {
      estimation: estimation
    });
  }
}

export default new SupportService();
