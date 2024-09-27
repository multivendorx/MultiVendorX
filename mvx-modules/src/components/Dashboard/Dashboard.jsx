import Header from "../Headers/Headers";

const Dashboard = () => {
    return (
        <>
            <Header />
            <div className="relative overflow-x-auto shadow-md mt-3">
                <table className="selectable custome-table">
                    <thead className="bg-slate-300 dark:bg-dark font-medium">
                        <tr className="table-head">
                            <td className="table-row-custom">
                                <div className="flex items-center">
                                    <input
                                    id="checkbox-all"
                                    type="checkbox"
                                    className="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                                    />
                                    <label htmlFor="checkbox-all" className="sr-only">
                                        checkbox
                                    </label>
                                </div>
                            </td>
                            <td className="table-row-custom">Subcription</td>
                            <td className="table-row-custom">Item</td>
                            <td className="table-row-custom">Total</td>
                            <td className="table-row-custom">Start Date</td>
                            <td className="table-row-custom">Trial End</td>
                            <td className="table-row-custom">Next Payment</td>
                            <td className="table-row-custom">Last Order Date</td>
                            <td className="table-row-custom">End Date</td>
                            <td className="table-row-custom">Order</td>
                            <td className="table-row-custom">Status</td>
                        </tr>
                    </thead>
                    <tbody>
                        {
                            paginatedData.map((item, index) => (
                                <tr
                                    key={index}
                                    className={`${isActive ? "active" : ""} relative`}
                                >
                    <td className="table-row-custom">
                      <div className="flex items-center">
                        <input
                          id="checkbox-all"
                          type="checkbox"
                          className="w-4 h-4 text-blue-600 bg-gray-100 border-gray-300 rounded focus:ring-blue-500 dark:focus:ring-blue-600 dark:ring-offset-gray-800 dark:focus:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600"
                        />
                        <label htmlFor="checkbox-all" className="sr-only">
                          checkbox
                        </label>
                      </div>
                    </td>
                    <td title={item.rating.count} className="table-row-custom">
                      <div>
                        <h1>Subcription</h1>
                        <p>#{truncateText(item.rating.count, 10)}</p>
                      </div>
                    </td>
                    <td title={item.title} className="table-row-custom">
                      <div>
                        <h1>Item</h1>
                        <p>{truncateText(item.title, 10)}</p>
                      </div>
                    </td>
                    <td title={item.price} className="table-row-custom">
                      <div>
                        <h1>Total</h1>
                        <p>{truncateText(item.price, 10)}</p>
                      </div>
                    </td>
                    <td title={item.rating.count} className="table-row-custom">
                      <div>
                        <h1>Subcription</h1>
                        <p>#{truncateText(item.rating.count, 10)}</p>
                      </div>
                    </td>
                    <td title={item.title} className="table-row-custom">
                      <div>
                        <h1>Item</h1>
                        <p>{truncateText(item.title, 10)}</p>
                      </div>
                    </td>
                    <td title={item.price} className="table-row-custom">
                      <div>
                        <h1>Total</h1>
                        <p>{truncateText(item.price, 10)}</p>
                      </div>
                    </td>
                    <td title={item.rating.count} className="table-row-custom">
                      <div>
                        <h1>Subcription</h1>
                        <p>#{truncateText(item.rating.count, 10)}</p>
                      </div>
                    </td>
                    <td title={item.title} className="table-row-custom">
                      <div>
                        <h1>Item</h1>
                        <p>{truncateText(item.title, 10)}</p>
                      </div>
                    </td>
                    <td title={item.price} className="table-row-custom">
                      <div>
                        <h1>Total</h1>
                        <p>{truncateText(item.price, 10)}</p>
                      </div>
                    </td>
                    <td title={item.price} className="table-row-custom">
                      <div>
                        <h1>Total</h1>
                        <p>{truncateText(item.price, 10)}</p>
                      </div>
                    </td>
                    <td className="dropdown_btn">
                      <button onClick={toggleActive}>
                      <i class={`${isActive ? "uil-eye" : "uil-eye-slash"} uil`}></i>
                      </button>
                    </td>
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        </>
        
    );
}

export default Dashboard;