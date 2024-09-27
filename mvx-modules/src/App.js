// import components
import AppRouter from "./components/AppRouter/AppRouter.jsx";

// import contexts providers
import { ThemeProvider } from "./contexts/ThemeContext.jsx";
import { ModuleProvider } from "./contexts/ModuleContext.jsx";

// css and scss file for global styling.
import "./styles/main.css";
import "./styles/variable.scss";

const App = () => {
    return (
        <>
            <ThemeProvider theme={ localStorage.getItem( 'mvxTheme' ) }>
                <ModuleProvider modules={ appLocalizer.moduleList }>
                    <AppRouter />
                </ModuleProvider>
            </ThemeProvider>
        </>
    );
}

export default App;