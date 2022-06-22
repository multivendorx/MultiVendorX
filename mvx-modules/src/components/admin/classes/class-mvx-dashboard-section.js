import React, { Component } from 'react';
import axios from 'axios';
import { css } from "@emotion/react";
import PuffLoader from "react-spinners/PuffLoader";
import HeaderSection from './class-mvx-page-header';

import {
  BrowserRouter as Router,
  useLocation
} from "react-router-dom";

const override = css`
  display: block;
  margin: 0 auto;
  border-color: red;
`;

class MVX_Dashboard extends Component {
  constructor(props) {
    super(props);
    this.state = {};
    this.QueryParamsDemo = this.QueryParamsDemo.bind(this);
    this.useQuery = this.useQuery.bind(this);
  }

  useQuery() {
    return new URLSearchParams(useLocation().hash);
  }

  QueryParamsDemo() {
    let use_query = this.useQuery();
    return (
      <div className="mvx-general-wrapper mvx-dashboard">
        <HeaderSection />
          <div className="mvx-sub-container mvx-container">
            <div className="mvx-middle-container-wrapper">
              <div className="mvx-dashboard-top-heading">Welcome to MultiVendorX</div>
              <div className='mvx-slider-content-main-wrapper'>
                
                <div className="mvx-dashboard-slider">
                  <div className="mvx-dashboard-top-icon">
                    <span>Pro</span>
                  </div>
                  <div className="mvx-pro-txt">
                    <h2>Activate MultiVendorX Pro License</h2>
                    <p>
                      Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                      tempor.
                    </p>
                    <a href="#" className="btn red-btn">
                      Active License
                    </a>
                  </div>
                </div>

                <div className="mvx-dashboard-slider mvx-flex-content">
                  <div className="mvx-dashboard-top-icon">
                    <span>Pro</span>
                  </div>
                  <div className="mvx-pro-txt">
                    <h2>02 Activate MultiVendorX Pro License</h2>
                    <p>
                      Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                      tempor.
                    </p>
                    <a href="#" className="btn red-btn">
                      Active License
                    </a>
                  </div>
                </div>

                <div className="mvx-dashboard-slider mvx-flex-content">
                  <div className="mvx-dashboard-top-icon">
                    <span>Pro</span>
                  </div>
                  <div className="mvx-pro-txt">
                    <h2>03 Activate MultiVendorX Pro License</h2>
                    <p>
                      Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                      tempor.
                    </p>
                    <a href="#" className="btn red-btn">
                      Active License
                    </a>
                  </div>
                </div>

                <div className="mvx-dashboard-slider mvx-flex-content">
                  <div className="mvx-dashboard-top-icon">
                    <span>Pro</span>
                  </div>
                  <div className="mvx-pro-txt">
                    <h2>04 Activate MultiVendorX Pro License</h2>
                    <p>
                      Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod
                      tempor.
                    </p>
                    <a href="#" className="btn red-btn">
                      Active License
                    </a>
                  </div>
                </div>

                <div className="border-block">
                  <a href="#" className="p-prev">
                    <img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAABSUlEQVRoge3VSUoDQRTG8ZT7JPRwAK/gsDKCi67qCIIXEEnyXhTidIWcwRlXegBv4bBSb2JA95q8lko32VXV5tnwftD7/9dUdTcaQgghhBBCeFDcAcHSDFZije/tbm+Zu8VbomGdnklicErPI3ePl7TbX6X4zyI+NvjRzPYT7iZnC/F0fFo5xtxNztJ8uFbFw1sd4yfz+PbOKOJuchblBxuJxi97YV/j7b0Wd5OzhXgNL/WKN9ihi/pt45+TXWhyNzmL9GCzjDf4VKv4Al3UOxs/pQt8yN3jbzxeomPzYI/PD/2wetxJ/v5G4H01AvrcSSEUvf1be5x+adCIOygEjYCbcoSBI+6gEIq+StfViOExd1AIGgFX8xE06IQ7KISiI3RZjsjglDsoBI3Ai3KEwTPuoBCKPq3ndR5QUKkebHFHCCGEEEKIf2IGz8t6OLWH744AAAAASUVORK5CYII=' />
                  </a>
                  <span>1 of 4</span>
                  <a href="#" className="p-next">
                    <img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAAwCAYAAABXAvmHAAAACXBIWXMAAAsTAAALEwEAmpwYAAABRklEQVRoge3XT0rDQBTH8fhn56JmMuDKW4jWVZS+mRsUFATte2k9Rs7h36VuPIO6U8GziIKu1OokGUvFTaYLHwPvA9l/fxAmkyQRQgghhBDCm+MOmFlqD1YV4KOytM7dMpPM0JV7vjLAJ/escfcE6+T7yy78oR5h8FmZwQZ3U7AURp0M6D7+EQbvJiOg6HI3Bfs1Augl7eEmd1OwFbO3pAzdViMU0KuGQc7dFKwZgTc/I7IebXE3BfszwuI2d1OwegTQdTMC36IcoW0xak6mesQRd08QbXDHnUrv/jW6TPJykbupNQ3FrjtKP+p4gxdJv7/A3dSasojuevHpP2rnSVnOcze15q4RNIkHOosqPgUqpuJPo4rXgMOp1+YkrvjqqPTx7hpxHFW8iz50zzjK+Ir/UI39RyrOf2N/44wzXgghhBBC/L9v4WR/hiGHPxkAAAAASUVORK5CYII=' />
                  </a>
                </div>

              </div>

              <div className='mvx-setup-documentation'>
              <div className="mvx-setup-marketing-white-box">
                <h3 className="mvx-block-title">This is what you get</h3>
                <ul className="mvx-table-ul">
                  <li className="mvx-align-items-center hover-border-box">
                    <div className="mvx-allign-li-txt">
                      <span>
                        <i className="mvx-font icon-chart-line" />
                      </span>{" "}
                      Set up marketing tools
                    </div>
                    <div className="li-action">
                      <a href="#" className="btn color-btn">
                        <i className="mvx-font icon-yes" />
                      </a>
                    </div>
                  </li>
                  <li className="mvx-align-items-center hover-border-box">
                    <div className="mvx-allign-li-txt">
                      <span>
                        <i className="mvx-font icon-chart-line" />
                      </span>{" "}
                      Set up marketing tools
                    </div>
                    <div className="li-action">
                      <a href="#" className="btn color-btn">
                        <i className="mvx-font icon-yes" />
                      </a>
                    </div>
                  </li>
                  <li className="mvx-align-items-center hover-border-box">
                    <div className="mvx-allign-li-txt">
                      <span>
                        <i className="mvx-font icon-chart-line" />
                      </span>{" "}
                      Set up marketing tools
                    </div>
                    <div className="li-action">
                      <a href="#" className="btn color-btn">
                        <i className="mvx-font icon-yes" />
                      </a>
                    </div>
                  </li>
                  <li className="mvx-align-items-center hover-border-box">
                    <div className="mvx-allign-li-txt">
                      <span>
                        <i className="mvx-font icon-chart-line" />
                      </span>{" "}
                      Set up marketing tools
                    </div>
                    <div className="li-action">
                      <a href="#" className="btn color-btn">
                        <i className="mvx-font icon-yes" />
                      </a>
                    </div>
                  </li>
                  <li className="mvx-align-items-center hover-border-box">
                    <div className="mvx-allign-li-txt">
                      <span>
                        <i className="mvx-font icon-chart-line" />
                      </span>{" "}
                      Set up marketing tools
                    </div>
                    <div className="li-action">
                      <a href="#" className="btn border-btn">
                        Setup
                      </a>
                    </div>
                  </li>
                  <li className="mvx-align-items-center hover-border-box">
                    <div className="mvx-allign-li-txt">
                      <span>
                        <i className="mvx-font icon-chart-line" />
                      </span>{" "}
                      Set up marketing tools
                    </div>
                    <div className="li-action">
                      <a href="#" className="btn color-btn">
                        <i className="mvx-font icon-yes" />
                      </a>
                    </div>
                  </li>
                </ul>
              </div>
              <div className='mvx-documentation-quick-link'>
                <div className='mvx-forum'>
                  <div className='mvx-documentation-support-forum'>
                  <figure>
                    <img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAA1CAYAAAAHz2g0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyVpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDYuMC1jMDAyIDc5LjE2NDQ2MCwgMjAyMC8wNS8xMi0xNjowNDoxNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIxLjIgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUVBRjlCQjVCQjBDMTFFQ0I3QkI4MjI5RkQ5MzZDQkQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUVBRjlCQjZCQjBDMTFFQ0I3QkI4MjI5RkQ5MzZDQkQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoxRUFGOUJCM0JCMEMxMUVDQjdCQjgyMjlGRDkzNkNCRCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDoxRUFGOUJCNEJCMEMxMUVDQjdCQjgyMjlGRDkzNkNCRCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PntVzLAAAARMSURBVHja7FpbSBRhFJ7d1ktJRaVWhoUpIhaZZoldqJeghyKSeoskqaAeggo0kDIiCJSohx66oBCR0EMPEQkmUUmZYqVgZHZDIcvKLm5WlqZ9R8/YadrZ2dGd3VnYAx//v2f/mTnffzvnvzgUH6SitMaFZC6QAmQxEoE5nJqRt8CARveN9e3AI+AB8BjoLShc6/VlDi9GU5IJlAAbleDIHWAf0KRHxKFjfByS68BSLy93Ay+BH1yj77xUUoJGNwWYyvmJQKwBkTpgPUh8NiQA43OQ1GvUD4EyoIEN7cPLhvxVzdzaTiCSu+Uy4CCwSFM0F9+t1yWAF2VyH1TlBrAdeGPUF/0tTIrGVzkgP74attT+RwAPUJN2Ay5W7QbOBNpwHSK7gLNCPRt2danNpha6IIzfbwfjSdiGc8AOoa6BzY5RApAkMdPQgDllB+M1JMp5YiFZCGRLAiWifL4/B6ifSewUqqPDBNhJ5bOyE3ih2FfI2TVxfh1sj6QWmC8KlNmp6+i0QqlQJRKBDKG4q9hf5DSfSgQWC8WrECAgPX6yk2cgVXpDgMB3kV9ABOYJxUAIEOgX+XSX5k/D6RMjPwpJNbl0g1rKw6CrtmIgwwY3B4SznB5GuZGkGRhPMomcoYWtoEal011jeLgFOAnkeCnTy3G8VdLJXT/WNYYmHORYyRZCXShOsAo5IQLRnB8MVQIhLWECYQLjFJeYhXxdo0YguaIJAv0hrcAGTNO/fPQDowSiTX4olT5kQWUm8jKxzmwLmJU24CKwxs8EbgONY+lCZj0xRazbwoM4TCBMIEzAH+IOVQLqeUIPTaPvgXg7Wsm7hunAKl4BJvNaOFX6gW4mEGMjw6chKQYO+OLIutUFsg0Mpy3zvWY2BGgMvBYvcAbReDpeqtUYT5u5BcrIsRMFkUQwSvz/kVrguVDQS/qCZHyL6NsUkeYBVdqtfpQd1BJoE4rJgSbA3eamMJ7Oh5fD8K86j8gWeEpdpl0oEoLQe/YAKzn/BMjyYrycQkmaiUCHUOQGuPbpYPE0/6Qol45R+w0ekxtqjUSgSygK+cAvUFIk8ltgvNuA8LCNchXn5J2286xIkk7C4tqfIAh8Aq768Bjd11jC+cu0/FSnzROiUKV6hGmxpIlYrNjoYJFtuiRUh2QwRzPRLc4Tw8MB6EpyvFX50HWKxGC/pk7/Tl4mUrJJ+XvAcQQosbglskW+y6DmKaw4LnzEVvUowCnWuj2if6kkGvGCFItaI03k+z3VOkAnqPeBY3IWkoPd020VuiHSoNlueaaM3Fap49CDLij9Hs+RLL5zjxyWiMmG2EnNBFbwbCNvqwywg/tn50LvvhB55ApgswmbyIN/8KCncP2nRkcnOGY2xmh80pHVF+0fRje2aFqlawj5QYrxKrn7tJq6seWBTAT7Bxp4GUwsXtTmDA+PxfgQotOApHPfHu6mFNA1c9rBe1Be5Y8AAwCQ3DTWJBG/vwAAAABJRU5ErkJggg==' />
                  </figure>
                  <figcaption>
                    <h2>Documentation Forum</h2>
                    <div>
                      <p>Further Clarification Visit Our</p>
                      <p>Document Forum</p>
                    </div>
                    <a href="#">
                      Visit Documentation Forum{" "}
                      <span className="mvx-font icon-down-arrow" />
                    </a>
                  </figcaption>
                  </div>


                  <div className='mvx-documentation-support-forum'>
                    <figure>
                      <img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAADAAAAA1CAYAAAAHz2g0AAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyVpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDYuMC1jMDAyIDc5LjE2NDQ2MCwgMjAyMC8wNS8xMi0xNjowNDoxNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIxLjIgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6MUVBRjlCQjVCQjBDMTFFQ0I3QkI4MjI5RkQ5MzZDQkQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6MUVBRjlCQjZCQjBDMTFFQ0I3QkI4MjI5RkQ5MzZDQkQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDoxRUFGOUJCM0JCMEMxMUVDQjdCQjgyMjlGRDkzNkNCRCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDoxRUFGOUJCNEJCMEMxMUVDQjdCQjgyMjlGRDkzNkNCRCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PntVzLAAAARMSURBVHja7FpbSBRhFJ7d1ktJRaVWhoUpIhaZZoldqJeghyKSeoskqaAeggo0kDIiCJSohx66oBCR0EMPEQkmUUmZYqVgZHZDIcvKLm5WlqZ9R8/YadrZ2dGd3VnYAx//v2f/mTnffzvnvzgUH6SitMaFZC6QAmQxEoE5nJqRt8CARveN9e3AI+AB8BjoLShc6/VlDi9GU5IJlAAbleDIHWAf0KRHxKFjfByS68BSLy93Ay+BH1yj77xUUoJGNwWYyvmJQKwBkTpgPUh8NiQA43OQ1GvUD4EyoIEN7cPLhvxVzdzaTiCSu+Uy4CCwSFM0F9+t1yWAF2VyH1TlBrAdeGPUF/0tTIrGVzkgP74attT+RwAPUJN2Ay5W7QbOBNpwHSK7gLNCPRt2danNpha6IIzfbwfjSdiGc8AOoa6BzY5RApAkMdPQgDllB+M1JMp5YiFZCGRLAiWifL4/B6ifSewUqqPDBNhJ5bOyE3ih2FfI2TVxfh1sj6QWmC8KlNmp6+i0QqlQJRKBDKG4q9hf5DSfSgQWC8WrECAgPX6yk2cgVXpDgMB3kV9ABOYJxUAIEOgX+XSX5k/D6RMjPwpJNbl0g1rKw6CrtmIgwwY3B4SznB5GuZGkGRhPMomcoYWtoEal011jeLgFOAnkeCnTy3G8VdLJXT/WNYYmHORYyRZCXShOsAo5IQLRnB8MVQIhLWECYQLjFJeYhXxdo0YguaIJAv0hrcAGTNO/fPQDowSiTX4olT5kQWUm8jKxzmwLmJU24CKwxs8EbgONY+lCZj0xRazbwoM4TCBMIEzAH+IOVQLqeUIPTaPvgXg7Wsm7hunAKl4BJvNaOFX6gW4mEGMjw6chKQYO+OLIutUFsg0Mpy3zvWY2BGgMvBYvcAbReDpeqtUYT5u5BcrIsRMFkUQwSvz/kVrguVDQS/qCZHyL6NsUkeYBVdqtfpQd1BJoE4rJgSbA3eamMJ7Oh5fD8K86j8gWeEpdpl0oEoLQe/YAKzn/BMjyYrycQkmaiUCHUOQGuPbpYPE0/6Qol45R+w0ekxtqjUSgSygK+cAvUFIk8ltgvNuA8LCNchXn5J2286xIkk7C4tqfIAh8Aq768Bjd11jC+cu0/FSnzROiUKV6hGmxpIlYrNjoYJFtuiRUh2QwRzPRLc4Tw8MB6EpyvFX50HWKxGC/pk7/Tl4mUrJJ+XvAcQQosbglskW+y6DmKaw4LnzEVvUowCnWuj2if6kkGvGCFItaI03k+z3VOkAnqPeBY3IWkoPd020VuiHSoNlueaaM3Fap49CDLij9Hs+RLL5zjxyWiMmG2EnNBFbwbCNvqwywg/tn50LvvhB55ApgswmbyIN/8KCncP2nRkcnOGY2xmh80pHVF+0fRje2aFqlawj5QYrxKrn7tJq6seWBTAT7Bxp4GUwsXtTmDA+PxfgQotOApHPfHu6mFNA1c9rBe1Be5Y8AAwCQ3DTWJBG/vwAAAABJRU5ErkJggg==' />
                    </figure>
                    <figcaption>
                      <h2>Support Forum</h2>
                      <div>
                        <p>Further Clarification Visit Our</p>
                        <p>Support Forum</p>
                      </div>
                      <a href="#">
                        Join Support Forum{" "}
                        <span className="mvx-font icon-down-arrow" />
                      </a>
                    </figcaption>
                  </div>
                </div>


              <div className='mvx-quick-link-sec'>
                <h3 className="block-title">Quick Link</h3>
                <ul className="row-link">
                  <li>
                    <a href="#">
                      <figure>
                        <img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyVpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDYuMC1jMDAyIDc5LjE2NDQ2MCwgMjAyMC8wNS8xMi0xNjowNDoxNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIxLjIgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6OTFFMzVDQTRCQjE1MTFFQ0I3QkI4MjI5RkQ5MzZDQkQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6OTFFMzVDQTVCQjE1MTFFQ0I3QkI4MjI5RkQ5MzZDQkQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo5MUUzNUNBMkJCMTUxMUVDQjdCQjgyMjlGRDkzNkNCRCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo5MUUzNUNBM0JCMTUxMUVDQjdCQjgyMjlGRDkzNkNCRCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmenBSIAAAGwSURBVHjajJRNKERRFMfnjbewM2lKFiNZMbtZ+FjM1s5HJmWrSVmTjyzsmMUQdiRmYWExyYIkWZCVInbYjTJSGg2ZJBH/k/8rXfe+d2/9Om/OPfc/555z3nNChpXLHoppAVNggO48yIDr9ESn9lzYR0wOXoF2MEna6Msw5t9yDQn2MLMhsO5lA5ElmLT4wCnYUQ86muzEVwK7YFC9GjPLgV4Qxf530JUjoBZkdXWib44xEZsaVtM+h8zrRYn1FXyljfsIxpVYX8EKOAFrqJerqbH4VhlTCWwKD0VhHsAlSIEitxrAFkiAetSzZDs2EjgOFsGdZn+UMcFziOwa+Ua0gg/O2gWoYmZdYIFdnjYKcr6GwQq44Vtxrs4Z4qTuzeBJl6HzR2yM8yX/OqsKGWotWccQe6t2OUExqc2MjRhXEhQo/CvIK+yDI2mC6StiWI46LVLDDlAnWdqIMZskRfro7oa/DPsogiPgjHNns2LgWPFt0xbkuv1g0/aqbECYpOiu4e8myXBPOouU32E/A/SkWQcQLfL6ZfrfvEa6/LxvgGXLK8sbNM/ne87sl7f5I8AAgZOIFhqgvjgAAAAASUVORK5CYII=' />
                      </figure>
                      Add Vendor
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <figure>
                        <img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyVpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDYuMC1jMDAyIDc5LjE2NDQ2MCwgMjAyMC8wNS8xMi0xNjowNDoxNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIxLjIgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6OTFFMzVDQTRCQjE1MTFFQ0I3QkI4MjI5RkQ5MzZDQkQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6OTFFMzVDQTVCQjE1MTFFQ0I3QkI4MjI5RkQ5MzZDQkQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo5MUUzNUNBMkJCMTUxMUVDQjdCQjgyMjlGRDkzNkNCRCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo5MUUzNUNBM0JCMTUxMUVDQjdCQjgyMjlGRDkzNkNCRCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmenBSIAAAGwSURBVHjajJRNKERRFMfnjbewM2lKFiNZMbtZ+FjM1s5HJmWrSVmTjyzsmMUQdiRmYWExyYIkWZCVInbYjTJSGg2ZJBH/k/8rXfe+d2/9Om/OPfc/555z3nNChpXLHoppAVNggO48yIDr9ESn9lzYR0wOXoF2MEna6Msw5t9yDQn2MLMhsO5lA5ElmLT4wCnYUQ86muzEVwK7YFC9GjPLgV4Qxf530JUjoBZkdXWib44xEZsaVtM+h8zrRYn1FXyljfsIxpVYX8EKOAFrqJerqbH4VhlTCWwKD0VhHsAlSIEitxrAFkiAetSzZDs2EjgOFsGdZn+UMcFziOwa+Ua0gg/O2gWoYmZdYIFdnjYKcr6GwQq44Vtxrs4Z4qTuzeBJl6HzR2yM8yX/OqsKGWotWccQe6t2OUExqc2MjRhXEhQo/CvIK+yDI2mC6StiWI46LVLDDlAnWdqIMZskRfro7oa/DPsogiPgjHNns2LgWPFt0xbkuv1g0/aqbECYpOiu4e8myXBPOouU32E/A/SkWQcQLfL6ZfrfvEa6/LxvgGXLK8sbNM/ne87sl7f5I8AAgZOIFhqgvjgAAAAASUVORK5CYII=' />
                      </figure>
                      Commission
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <figure>
                        <img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyVpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDYuMC1jMDAyIDc5LjE2NDQ2MCwgMjAyMC8wNS8xMi0xNjowNDoxNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIxLjIgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6OTFFMzVDQTRCQjE1MTFFQ0I3QkI4MjI5RkQ5MzZDQkQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6OTFFMzVDQTVCQjE1MTFFQ0I3QkI4MjI5RkQ5MzZDQkQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo5MUUzNUNBMkJCMTUxMUVDQjdCQjgyMjlGRDkzNkNCRCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo5MUUzNUNBM0JCMTUxMUVDQjdCQjgyMjlGRDkzNkNCRCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmenBSIAAAGwSURBVHjajJRNKERRFMfnjbewM2lKFiNZMbtZ+FjM1s5HJmWrSVmTjyzsmMUQdiRmYWExyYIkWZCVInbYjTJSGg2ZJBH/k/8rXfe+d2/9Om/OPfc/555z3nNChpXLHoppAVNggO48yIDr9ESn9lzYR0wOXoF2MEna6Msw5t9yDQn2MLMhsO5lA5ElmLT4wCnYUQ86muzEVwK7YFC9GjPLgV4Qxf530JUjoBZkdXWib44xEZsaVtM+h8zrRYn1FXyljfsIxpVYX8EKOAFrqJerqbH4VhlTCWwKD0VhHsAlSIEitxrAFkiAetSzZDs2EjgOFsGdZn+UMcFziOwa+Ua0gg/O2gWoYmZdYIFdnjYKcr6GwQq44Vtxrs4Z4qTuzeBJl6HzR2yM8yX/OqsKGWotWccQe6t2OUExqc2MjRhXEhQo/CvIK+yDI2mC6StiWI46LVLDDlAnWdqIMZskRfro7oa/DPsogiPgjHNns2LgWPFt0xbkuv1g0/aqbECYpOiu4e8myXBPOouU32E/A/SkWQcQLfL6ZfrfvEa6/LxvgGXLK8sbNM/ne87sl7f5I8AAgZOIFhqgvjgAAAAASUVORK5CYII=' />
                      </figure>
                      Add Product
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <figure>
                        <img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyVpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDYuMC1jMDAyIDc5LjE2NDQ2MCwgMjAyMC8wNS8xMi0xNjowNDoxNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIxLjIgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6OTFFMzVDQTRCQjE1MTFFQ0I3QkI4MjI5RkQ5MzZDQkQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6OTFFMzVDQTVCQjE1MTFFQ0I3QkI4MjI5RkQ5MzZDQkQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo5MUUzNUNBMkJCMTUxMUVDQjdCQjgyMjlGRDkzNkNCRCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo5MUUzNUNBM0JCMTUxMUVDQjdCQjgyMjlGRDkzNkNCRCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmenBSIAAAGwSURBVHjajJRNKERRFMfnjbewM2lKFiNZMbtZ+FjM1s5HJmWrSVmTjyzsmMUQdiRmYWExyYIkWZCVInbYjTJSGg2ZJBH/k/8rXfe+d2/9Om/OPfc/555z3nNChpXLHoppAVNggO48yIDr9ESn9lzYR0wOXoF2MEna6Msw5t9yDQn2MLMhsO5lA5ElmLT4wCnYUQ86muzEVwK7YFC9GjPLgV4Qxf530JUjoBZkdXWib44xEZsaVtM+h8zrRYn1FXyljfsIxpVYX8EKOAFrqJerqbH4VhlTCWwKD0VhHsAlSIEitxrAFkiAetSzZDs2EjgOFsGdZn+UMcFziOwa+Ua0gg/O2gWoYmZdYIFdnjYKcr6GwQq44Vtxrs4Z4qTuzeBJl6HzR2yM8yX/OqsKGWotWccQe6t2OUExqc2MjRhXEhQo/CvIK+yDI2mC6StiWI46LVLDDlAnWdqIMZskRfro7oa/DPsogiPgjHNns2LgWPFt0xbkuv1g0/aqbECYpOiu4e8myXBPOouU32E/A/SkWQcQLfL6ZfrfvEa6/LxvgGXLK8sbNM/ne87sl7f5I8AAgZOIFhqgvjgAAAAASUVORK5CYII=' />
                      </figure>
                      Payment
                    </a>
                  </li>
                  <li>
                    <a href="#">
                      <figure>
                        <img src='data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABQAAAAUCAYAAACNiR0NAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAAAyVpVFh0WE1MOmNvbS5hZG9iZS54bXAAAAAAADw/eHBhY2tldCBiZWdpbj0i77u/IiBpZD0iVzVNME1wQ2VoaUh6cmVTek5UY3prYzlkIj8+IDx4OnhtcG1ldGEgeG1sbnM6eD0iYWRvYmU6bnM6bWV0YS8iIHg6eG1wdGs9IkFkb2JlIFhNUCBDb3JlIDYuMC1jMDAyIDc5LjE2NDQ2MCwgMjAyMC8wNS8xMi0xNjowNDoxNyAgICAgICAgIj4gPHJkZjpSREYgeG1sbnM6cmRmPSJodHRwOi8vd3d3LnczLm9yZy8xOTk5LzAyLzIyLXJkZi1zeW50YXgtbnMjIj4gPHJkZjpEZXNjcmlwdGlvbiByZGY6YWJvdXQ9IiIgeG1sbnM6eG1wPSJodHRwOi8vbnMuYWRvYmUuY29tL3hhcC8xLjAvIiB4bWxuczp4bXBNTT0iaHR0cDovL25zLmFkb2JlLmNvbS94YXAvMS4wL21tLyIgeG1sbnM6c3RSZWY9Imh0dHA6Ly9ucy5hZG9iZS5jb20veGFwLzEuMC9zVHlwZS9SZXNvdXJjZVJlZiMiIHhtcDpDcmVhdG9yVG9vbD0iQWRvYmUgUGhvdG9zaG9wIDIxLjIgKE1hY2ludG9zaCkiIHhtcE1NOkluc3RhbmNlSUQ9InhtcC5paWQ6OTFFMzVDQTRCQjE1MTFFQ0I3QkI4MjI5RkQ5MzZDQkQiIHhtcE1NOkRvY3VtZW50SUQ9InhtcC5kaWQ6OTFFMzVDQTVCQjE1MTFFQ0I3QkI4MjI5RkQ5MzZDQkQiPiA8eG1wTU06RGVyaXZlZEZyb20gc3RSZWY6aW5zdGFuY2VJRD0ieG1wLmlpZDo5MUUzNUNBMkJCMTUxMUVDQjdCQjgyMjlGRDkzNkNCRCIgc3RSZWY6ZG9jdW1lbnRJRD0ieG1wLmRpZDo5MUUzNUNBM0JCMTUxMUVDQjdCQjgyMjlGRDkzNkNCRCIvPiA8L3JkZjpEZXNjcmlwdGlvbj4gPC9yZGY6UkRGPiA8L3g6eG1wbWV0YT4gPD94cGFja2V0IGVuZD0iciI/PmenBSIAAAGwSURBVHjajJRNKERRFMfnjbewM2lKFiNZMbtZ+FjM1s5HJmWrSVmTjyzsmMUQdiRmYWExyYIkWZCVInbYjTJSGg2ZJBH/k/8rXfe+d2/9Om/OPfc/555z3nNChpXLHoppAVNggO48yIDr9ESn9lzYR0wOXoF2MEna6Msw5t9yDQn2MLMhsO5lA5ElmLT4wCnYUQ86muzEVwK7YFC9GjPLgV4Qxf530JUjoBZkdXWib44xEZsaVtM+h8zrRYn1FXyljfsIxpVYX8EKOAFrqJerqbH4VhlTCWwKD0VhHsAlSIEitxrAFkiAetSzZDs2EjgOFsGdZn+UMcFziOwa+Ua0gg/O2gWoYmZdYIFdnjYKcr6GwQq44Vtxrs4Z4qTuzeBJl6HzR2yM8yX/OqsKGWotWccQe6t2OUExqc2MjRhXEhQo/CvIK+yDI2mC6StiWI46LVLDDlAnWdqIMZskRfro7oa/DPsogiPgjHNns2LgWPFt0xbkuv1g0/aqbECYpOiu4e8myXBPOouU32E/A/SkWQcQLfL6ZfrfvEa6/LxvgGXLK8sbNM/ne87sl7f5I8AAgZOIFhqgvjgAAAAASUVORK5CYII=' />
                      </figure>
                      Add New
                    </a>
                  </li>
                </ul>
              </div>

            </div>
            </div>


            <div className='mvx-upgrade-pro-section'>
              <div className="mvx-pro-title"><div className="mvx-dashboard-top-icon"><span>Pro</span></div></div>
              <h2>Get more by Switching to Pro</h2>
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt.</p>
              <a href="#" className="btn red-btn">Upgrade to Pro</a>
            </div>

    

            <div className="mvx-text-center">
              <h2>Here Is What You Get In Pro Compared to Free</h2>
              <ul className="mvx-compare-table-holder">

                <li>
                  <ul>
                    <li />
                    <li>Support</li>
                    <li>2 Premium Modules</li>
                    <li>Store Widgets</li>
                    <li>Premium</li>
                    <li>Modules</li>
                    <li>Support</li>
                    <li>
                      <a href="#">
                        <span>
                          <i className="mvx-font icon-down-arrow-02" />
                        </span>{" "}
                        Show More
                      </a>
                    </li>
                  </ul>
                </li>

                <li>
                  <ul>
                    <li>Free</li>
                    <li>Ticket Based Support</li>
                    <li>
                      <i className="mvx-font icon-no red" />
                    </li>
                    <li>
                      <i className="mvx-font icon-no red" />
                    </li>
                    <li>Five Venders</li>
                    <li>
                      <i className="mvx-font icon-yes blue" />
                    </li>
                    <li>
                      <i className="mvx-font icon-no red" />
                    </li>
                    <li />
                  </ul>
                </li>

                <li>
                  <span className="recommend-tag">Recommend</span>
                  <ul>
                    <li>Pro</li>
                    <li>Ticket Based Support</li>
                    <li>
                      <i className="mvx-font icon-no red" />
                    </li>
                    <li>
                      <i className="mvx-font icon-no red" />
                    </li>
                    <li>Five Venders</li>
                    <li>
                      <i className="mvx-font icon-yes blue" />
                    </li>
                    <li>
                      <i className="mvx-font icon-no red" />
                    </li>
                    <li />
                  </ul>
                </li>

              </ul>
            </div>


            <div className="mvx-text-center">
              <h2>
                <span className="mvx-gra-txt">30 Days</span> Money-Back Guarantee
              </h2>
              <p>
                Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do
                eiusmod tempor incididunt.
              </p>
                
              <ul className="mvx-money-table-holder">
                <li>
                  <ul>
                    <li>Yearly</li>
                    <li>
                      <div className="m-price">
                        $399 <sub>/ $599</sub>
                      </div>
                    </li>
                    <li>
                      <a href="#" className="btn border-btn no-background">
                        Buy Now
                      </a>
                    </li>
                    <li>
                      <span className="mvx-font icon-form-radio" /> 10 Sites
                    </li>
                    <li>
                      <span className="mvx-font icon-form-radio" /> 50+ Modules
                    </li>
                    <li>
                      <span className="mvx-font icon-form-radio" /> Unlimited
                      Support{" "}
                    </li>
                    <li>
                      <span className="mvx-font icon-form-radio" /> Lifetime Updates
                    </li>
                    <li>
                      <a href="#" className="show-link">
                        <span>
                          <i className="mvx-font icon-down-arrow-02" />
                        </span>{" "}
                        Show More
                      </a>
                    </li>
                  </ul>
                </li>

                <li>
                  <span className="mvx-recommend-tag">Super saver</span>
                  <ul>
                    <li>Lifetime</li>
                    <li>
                      <div className="m-price">
                        $499 <sub>/ $599</sub>
                      </div>
                    </li>
                    <li>
                      <a href="#" className="btn red-btn">
                        Buy Now
                      </a>
                    </li>
                    <li>
                      <span className="mvx-font icon-form-radio" /> 10 Sites
                    </li>
                    <li>
                      <span className="mvx-font icon-form-radio" /> 50+ Modules
                    </li>
                    <li>
                      <span className="mvx-font icon-form-radio" /> Unlimited
                      Support{" "}
                    </li>
                    <li>
                      <span className="mvx-font icon-form-radio" /> Lifetime Updates
                    </li>
                    <li>
                      <a href="#" className="show-link">
                        <span>
                          <i className="mvx-font icon-down-arrow-02" />
                        </span>{" "}
                        Show More
                      </a>
                    </li>
                  </ul>
                </li>

                <li>
                  <ul>
                    <li>Monthly</li>
                    <li>
                      <div className="m-price">
                        $299 <sub>/ $599</sub>
                      </div>
                    </li>
                    <li>
                      <a href="#" className="btn border-btn no-background">
                        Buy Now
                      </a>
                    </li>
                    <li>
                      <span className="mvx-font icon-form-radio" /> 10 Sites
                    </li>
                    <li>
                      <span className="mvx-font icon-form-radio" /> 50+ Modules
                    </li>
                    <li>
                      <span className="mvx-font icon-form-radio" /> Unlimited
                      Support{" "}
                    </li>
                    <li>
                      <span className="mvx-font icon-form-radio" /> Lifetime Updates
                    </li>
                    <li>
                      <a href="#" className="show-link">
                        <span>
                          <i className="mvx-font icon-down-arrow-02" />
                        </span>{" "}
                        Show More
                      </a>
                    </li>
                  </ul>
                </li>
              </ul>
            </div>


            <div className="mvx-upgrade-pro-section">
              <div className="mvx-dashboard-top-icon"><span>Pro</span></div>
              <h2>Get to Go?</h2>
              <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt.</p>
              <a href="#" className="btn red-btn">Upgrade to Pro</a>
            </div>
            </div>
          </div>

            

        </div>
    );
  }

  render() {
    return (
      <Router>
        <this.QueryParamsDemo />
      </Router>
    );
  }
}
export default MVX_Dashboard;