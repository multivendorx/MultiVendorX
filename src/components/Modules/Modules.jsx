import { useState, useEffect } from "react";
import Dialog from "@mui/material/Dialog";
import Popoup from "../PopupContent/PopupContent";

import { getApiLink, sendApiResponse } from "../../services/apiService";
import { useModules } from "../../contexts/ModuleContext";
// import context
import { getModuleData } from "../../services/templateService";
import "./modules.scss";
import Section from "../AdminLibrary/Inputs/Util/Section";

const Modules = () => {
  const { modules, insertModule, removeModule } = useModules();
  
  const modulesArray = getModuleData();
  const [modelOpen, setModelOpen] = useState(false);
  const [successMsg, setSuccessMsg] = useState("");
  const [searchingValue, setSearchingValue] = useState("");
  const [categorySearchValue, setCategorySearchValue] = useState("");
  const categories = [];

  /**
   * Check whether a module is active or not.
   * @param {*} moduleId 
   * @returns 
   */
  const isModuleAvialable = ( moduleId ) => {
    const module = modulesArray.flatMap(category=>category.modules).find((module) => module.id === moduleId);
    const activeAllRequiredPlugins = [...new Set(module.required_plugin_list?.map(plugin=>plugin.is_active))];
    if ( ! module?.pro_module ) return true;
    if ( module?.pro_module && activeAllRequiredPlugins.length<2 && activeAllRequiredPlugins[0]===true) return true;
    return false;
  }

  /**
   * Handle module activation and deactivation.
   * @param {*} event 
   * @param {*} moduleId 
   * @returns 
   */
  const handleOnChange = async (event, moduleId) => {
    if ( ! isModuleAvialable( moduleId ) ) {
      setModelOpen(true);
      return;
    }

    const action = event.target.checked ? "activate" : "deactivate";
    if (action == "activate") {
      insertModule(moduleId);
    } else {
      removeModule(moduleId);
    }
    
    const response = await sendApiResponse(getApiLink("modules"), {
      id: moduleId,
      action,
    });
    if(!response){
      removeModule(moduleId);
      return;
    }
    setSuccessMsg('Module activated');
    setTimeout(() => setSuccessMsg(''), 2000);
  };

  /**
   * Handle module Searching.
   * @param {*} event 
   * @returns 
   */
  const handleSearch = (event)=>{
    setSearchingValue(event.target.value);
  }

  /**
   * Set Modules to Display.
   */
  const searchedModules = modulesArray.map(moduleData => (
    categories.push(moduleData.parent_category),
    {
    ...moduleData, 
    modules: moduleData.modules.filter(module => 
        module.id.toLocaleLowerCase().includes(searchingValue.toLocaleLowerCase())
    )
  })).filter(moduleData => moduleData.modules.length > 0 && (categorySearchValue!==""?(moduleData.parent_category.toLowerCase().replace(/\s+/g, "_")===categorySearchValue):true));

  /**
   * Handle module Searching By Category.
   * @param {*} event 
   * @returns 
   */
  const handleCategorySearch = (event)=>{
    setCategorySearchValue(event.target.value);
  }

  return (
    <>
      <div className="search">
        <input type="text" name="search" id="search" onChange={handleSearch} value={searchingValue}/>
      </div>
      <div className="searchByCategory">
        <select name="search_by_category" id="search_by_category" onChange={handleCategorySearch}>
          <option value="">select by category</option>
          {categories.map((category)=>{
            return <option value={category.toLowerCase().replace(/\s+/g, "_")}>{category}</option>
          })}
        </select>
      </div>
      <div className="module-container">
        <Dialog
          className="admin-module-popup"
          open={modelOpen}
          onClose={() => setModelOpen(false) }
          aria-labelledby="form-dialog-title"
        >
          <span
            className="admin-font adminLib-cross"
            onClick={() => setModelOpen(false) }
          ></span>
          <Popoup />
        </Dialog>

        {successMsg && (
          <div className="admin-notice-display-title">
            <i className="admin-font adminLib-icon-yes"></i>
            {successMsg}
          </div>
        )}
        
        <div className="tab-name">
          <h1>Modules</h1>
        </div>
        {searchedModules.map((moduleData) => (
          <>
          <Section wrapperClass="setting-section-divider" value={moduleData.parent_category} hint={moduleData.hint}/>
          <div className="module-option-row">
          {moduleData.modules.map((module)=>(
              <div className="module-list-item">
                {module.pro_module && !appLocalizer.khali_dabba && <span className="admin-pro-tag">Pro</span>}
                <div className="module-icon">
                  <i className={`font ${module.icon}`}></i>
                </div>

                <div className="card-meta">
                  <div className="meta-name">{module.name}</div>
                  <p className="meta-description" dangerouslySetInnerHTML={{ __html: module.desc }}></p>
                  {module.required_plugin_list?.length>0 && 
                    <fieldset>
                      <legend>
                        <i class="adminLib-pro-tab">
                        </i> Requires
                      </legend>
                      {module.required_plugin_list.map((plugin)=>{
                        return <>
                          <span>
                            {/*for close : adminLib-close */}
                            {/*for correct : adminLib-check */}
                            <span className={plugin.is_active?'adminLib-check':'adminLib-close'}></span>
                            <a href={plugin.plugin_link} >{plugin.plugin_name}</a>
                          </span>
                          <br />
                        </>
                      })}
                    </fieldset>
                  }
                </div>
                <div className="card-footer">
                  <div className="card-support">
                    <a href={module.doc_link} className="main-btn btn-purple card-support-btn">Docs</a>
                    <a href={module.settings_link} className="main-btn btn-purple card-support-btn">Setting</a>
                  </div>
                  <div className="toggle-checkbox-content" data={`${module.id}-showcase-tour`}>
                    <input
                      type="checkbox"
                      className="woo-toggle-checkbox"
                      id={`toggle-switch-${module.id}`}
                      checked={modules.includes(module.id)}
                      onChange={(e) => handleOnChange(e, module.id)}
                    />
                    <label htmlFor={`toggle-switch-${module.id}`} className="toggle-switch-is_hide_cart_checkout"></label>
                  </div>
                </div>
              </div>
            ))}
          </div>
         </>
        ))}
      </div>
    </>
  );
};

export default Modules;
