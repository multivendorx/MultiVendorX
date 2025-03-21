import React from 'react'
import Dialog from "@mui/material/Dialog";
import Popoup from "../PopupContent/PopupContent";
import { useState } from 'react';
import "./Advertising.scss";

const Advertising = () => {
    const [openDialog, setOpenDialog] = useState(false);
    return (
        <>
            {!appLocalizer.is_mvx_pro_active ? (
                <>
                    <Dialog
                        className="admin-module-popup"
                        open={openDialog}
                        onClose={() => {
                            setOpenDialog(false);
                        }}
                        aria-labelledby="form-dialog-title"
                    >
                        <span
                            className="admin-font adminLib-cross"
                            onClick={() => {
                                setOpenDialog(false);
                            }}
                        ></span>
                        <Popoup />
                    </Dialog>
                    <div
                        className="enquiry-img"
                        onClick={() => {
                            setOpenDialog(true);
                        }}>
                    </div>
                </>
            )
                : (
                    <>Hello</>
                )}
        </>
    )
}

export default Advertising