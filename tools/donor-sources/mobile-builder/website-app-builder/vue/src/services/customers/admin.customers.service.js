import api from "../api";

class AdminCustomersService {

  getCustomers(offset, search) {
    return api.get(`admin/users/list?offset=${offset}&search=${search}`, {});
  }

  getCustomer(id) {
    return api.get(`admin/users/detail?id=${id}`, {});
  }

  updateCustomer(id, data) {
    return api.post(`admin/users/update?id=${id}`, {
      email: data.email,
      admin: data.admin,
      new_password: data.new_password
    });
  }
}

export default new AdminCustomersService();
